# generate_blog.py

import subprocess
import json
from jinja2 import Environment, FileSystemLoader
import os
import shlex
import time
import markdown  # Optional: For Markdown to HTML conversion
import logging
import requests
from requests.auth import HTTPBasicAuth
import configparser
import faiss
from sentence_transformers import SentenceTransformer
import numpy as np


# Configure logging
LOG_FILE = 'autoblogger.log'
logging.basicConfig(
    filename=LOG_FILE,
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)
logging.info("Starting autoblogger configuration setup.")

# Load configuration
CONFIG_FILE = os.path.join(os.getcwd(), 'config.ini')
config = configparser.ConfigParser()

# Check if configuration file exists
if not os.path.exists(CONFIG_FILE):
    logging.critical(f"Configuration file not found: {CONFIG_FILE}")
    raise FileNotFoundError(f"Configuration file not found: {CONFIG_FILE}")

logging.info(f"Loading configuration from: {CONFIG_FILE}")
config.read(CONFIG_FILE)

# WordPress configuration
try:
    WORDPRESS_URL = config.get('wordpress', 'url')
    WORDPRESS_USERNAME = config.get('wordpress', 'username')
    WORDPRESS_PASSWORD = config.get('wordpress', 'password')
    DEFAULT_CATEGORIES = json.loads(config.get('wordpress', 'categories', fallback='[]'))
    DEFAULT_TAGS = json.loads(config.get('wordpress', 'tags', fallback='[]'))
    POST_STATUS = config.get('wordpress', 'status', fallback='draft').strip().lower()

    if POST_STATUS not in ['publish', 'draft']:
        logging.error(f"Invalid POST_STATUS value: {POST_STATUS}. Defaulting to 'draft'.")
        POST_STATUS = 'draft'

    logging.info("WordPress configuration loaded successfully.")
except configparser.NoSectionError as e:
    logging.critical(f"Missing required section in configuration file: {e}")
    raise
except configparser.NoOptionError as e:
    logging.critical(f"Missing required option in configuration file: {e}")
    raise
except json.JSONDecodeError as e:
    logging.critical(f"Error parsing JSON fields in configuration file: {e}")
    raise

# Vector database configuration
try:
    VECTOR_DB_DIMENSION = config.getint('vector_db', 'dimension', fallback=768)
    VECTOR_DB_INDEX_FILE = config.get('vector_db', 'index_file', fallback='vector_store.index').strip()
    VECTOR_DB_METADATA_FILE = config.get('vector_db', 'metadata_file', fallback='vector_metadata.json').strip()

    if VECTOR_DB_DIMENSION <= 0:
        logging.error(f"Invalid VECTOR_DB_DIMENSION value: {VECTOR_DB_DIMENSION}. Defaulting to 768.")
        VECTOR_DB_DIMENSION = 768

    logging.info("Vector database configuration loaded successfully.")
except configparser.NoSectionError as e:
    logging.critical(f"Missing required section in configuration file: {e}")
    raise
except configparser.NoOptionError as e:
    logging.critical(f"Missing required option in configuration file: {e}")
    raise
except ValueError as e:
    logging.critical(f"Invalid value in configuration file: {e}")
    raise

logging.info("Configuration setup completed successfully.")

# Initialize or load FAISS index
def initialize_faiss():
    if os.path.exists(VECTOR_DB_INDEX_FILE):
        index = faiss.read_index(VECTOR_DB_INDEX_FILE)
        logging.info("FAISS index loaded successfully.")
    else:
        index = faiss.IndexFlatL2(VECTOR_DB_DIMENSION)
        faiss.write_index(index, VECTOR_DB_INDEX_FILE)
        logging.info("FAISS index initialized and saved.")
    return index

# Load or initialize metadata
def load_metadata():
    if os.path.exists(VECTOR_DB_METADATA_FILE):
        with open(VECTOR_DB_METADATA_FILE, 'r', encoding='utf-8') as f:
            metadata = json.load(f)
        logging.info("Metadata loaded successfully.")
    else:
        metadata = []
        with open(VECTOR_DB_METADATA_FILE, 'w', encoding='utf-8') as f:
            json.dump(metadata, f)
        logging.info("Metadata file initialized.")
    return metadata

# Save metadata
def save_metadata(metadata):
    with open(VECTOR_DB_METADATA_FILE, 'w', encoding='utf-8') as f:
        json.dump(metadata, f, indent=2)
    logging.info("Metadata saved successfully.")

# Generate embeddings using Sentence Transformers
def generate_embeddings(texts):
    try:
        model = SentenceTransformer('all-MiniLM-L6-v2')  # Lightweight and efficient
        embeddings = model.encode(texts, convert_to_numpy=True)
        return embeddings
    except Exception as e:
        logging.error(f"Error generating embeddings: {e}")
        return None

def run_ollama(prompt):
    """
    Runs the Ollama Mistral model with the given prompt and returns the response.
    """
    try:
        command = f'ollama run mistral:latest "{prompt}"'
        logging.info(f'Running command: {command}')
        args = shlex.split(command)
        result = subprocess.run(
            args,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            encoding='utf-8',
            check=True
        )
        response = result.stdout.strip()
        logging.info(f'Ollama response: {response}')
        return response
    except subprocess.CalledProcessError as e:
        logging.error(f"Error running Ollama: {e.stderr.strip()}")
        return ""

def generate_content():
    """
    Generates content for different sections of the blog post using Ollama's Mistral model.
    """
    content = {}

    # Define prompts for each section tailored to freerideinvestor
    prompts = {
        "post_title": "Generate an engaging blog post title about automating blog posts with AI in the context of investment strategies.",
        "post_subtitle": "Generate a subtitle for the blog post titled 'Automating Blog Posts with AI in Investment Strategies'.",
        "introduction": "Write an introduction section for a blog post about automating blog posts using AI, specifically focusing on investment strategies. The introduction should be engaging and set the stage for the rest of the post.",
        "sections": [
            {
                "title": "Choosing the Right Templating Engine",
                "prompt": "Explain how to choose the right templating engine for an autoblogger, highlighting the benefits of using Python's Jinja2 in the context of investment blogs."
            },
            {
                "title": "Integrating AI for Content Generation",
                "prompt": "Describe how to integrate AI models like Mistral into an autoblogger to generate dynamic content for investment strategies."
            },
            {
                "title": "Automating the Deployment Process",
                "prompt": "Provide a detailed explanation on automating the deployment process of the generated blog posts to a web server, specifically for investment-related content."
            }
        ],
        "image": {
            "title": "Visual Representation of the Automation Process",
            "prompt": "Describe an image that visually represents the process of automating blog post generation using AI, tailored for an investment-focused blog."
        },
        "conclusion": "Write a conclusion for the blog post on automating blog posts with AI in investment strategies. The conclusion should summarize the key points and encourage readers to implement the strategies discussed.",
        "cta": {
            "title": "Stay Updated with the Latest AI Blogging Techniques for Investments",
            "content": "Subscribe to our newsletter to receive the latest updates, tutorials, and insights on automating your blogging process with AI in the investment sector.",
            "form_action": "/subscribe"
        }
    }

    # Generate each content section
    content['post_title'] = run_ollama(prompts['post_title']) or "Automating Blog Posts with AI in Investment Strategies: A Comprehensive Guide"
    content['post_subtitle'] = run_ollama(prompts['post_subtitle']) or "Leveraging Artificial Intelligence to Streamline Your Investment Content Workflow"

    introduction_text = run_ollama(prompts['introduction'])
    content['introduction'] = {
        "title": "Introduction",
        "content": introduction_text or "Welcome to this comprehensive guide on automating your blog posts using AI, specifically tailored for investment strategies. In today's digital age, leveraging artificial intelligence can significantly streamline your content creation process, saving you time and enhancing productivity."
    }

    content['sections'] = []
    for section in prompts['sections']:
        section_title = section['title']
        section_content = run_ollama(section['prompt'])
        if not section_content:
            section_content = f"Content for '{section_title}' goes here."
        content['sections'].append({
            "title": section_title,
            "content": section_content
        })
        time.sleep(1)  # Sleep to prevent overwhelming the AI model

    image_description = run_ollama(prompts['image']['prompt'])
    image_url = "https://via.placeholder.com/800x400.png?text=Automation+Process"
    content['image'] = {
        "title": prompts['image']['title'],
        "url": image_url,
        "alt": image_description or "An illustrative diagram showing the flow of automating blog post creation using AI models like Mistral for investment strategies."
    }

    conclusion_text = run_ollama(prompts['conclusion'])
    content['conclusion'] = {
        "title": "Conclusion",
        "content": conclusion_text or "In conclusion, automating your blog posts with AI can revolutionize your content creation process, especially in the realm of investment strategies. By integrating powerful models like Mistral, you can produce high-quality content efficiently, allowing you to focus more on strategy and less on repetitive tasks."
    }

    content['cta'] = prompts['cta']
    return content

def render_template(content):
    """
    Renders the blog_template.html with the provided content.
    """
    try:
        base_dir = os.path.dirname(os.path.abspath(__file__))
        env = Environment(loader=FileSystemLoader(base_dir))
        template = env.get_template('blog_template.html')
        logging.info(f"Rendering template with content: {json.dumps(content, indent=2)}")
        rendered_html = template.render(content)
        logging.info("Template rendered successfully.")
        return rendered_html
    except Exception as e:
        logging.error(f"Error rendering template: {e}")
        return ""

def save_output(rendered_html, post_title):
    """
    Saves the rendered HTML to the output directory with a filename based on the post title.
    """
    try:
        base_dir = os.path.dirname(os.path.abspath(__file__))
        output_dir = os.path.join(base_dir, 'output')
        os.makedirs(output_dir, exist_ok=True)
        safe_title = ''.join(c for c in post_title if c.isalnum() or c in (' ', '_')).rstrip()
        safe_title = safe_title.strip().replace(' ', '_').lower()
        max_length = 100
        if len(safe_title) > max_length:
            safe_title = safe_title[:max_length].rstrip('_')
        timestamp = time.strftime("%Y%m%d%H%M%S")
        filename = f"{safe_title}_{timestamp}.html"
        output_path = os.path.join(output_dir, filename)
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write(rendered_html)
        logging.info(f"Blog post generated successfully: {output_path}")
        print(f"Blog post generated successfully: {output_path}")
        return output_path
    except Exception as e:
        logging.error(f"Error saving output file: {e}")
        return ""

def post_to_wordpress(title, content, excerpt, categories, tags, image_url=None):
    """
    Posts the generated blog content to a WordPress site using the REST API.
    """
    try:
        wordpress_url = WORDPRESS_URL
        username = WORDPRESS_USERNAME
        password = WORDPRESS_PASSWORD
        auth = HTTPBasicAuth(username, password)

        def get_or_create_term(endpoint, name):
            term_url = f"{wordpress_url}/{endpoint}"
            params = {'search': name}
            response = requests.get(term_url, params=params, auth=auth)
            if response.status_code == 200:
                terms = response.json()
                for term in terms:
                    if term['name'].lower() == name.lower():
                        return term['id']
                # Term not found, create it
                payload = {'name': name}
                response = requests.post(term_url, json=payload, auth=auth)
                if response.status_code == 201:
                    return response.json()['id']
                else:
                    logging.error(f"Failed to create term '{name}': {response.text}")
            else:
                logging.error(f"Failed to get term '{name}': {response.text}")
            return None

        category_ids = [get_or_create_term('categories', cat) for cat in categories]
        tag_ids = [get_or_create_term('tags', tag) for tag in tags]

        post_data = {
            "title": title,
            "content": content,
            "excerpt": excerpt,
            "status": POST_STATUS,  # 'publish' or 'draft'
            "categories": [cid for cid in category_ids if cid],
            "tags": [tid for tid in tag_ids if tid],
        }

        if image_url:
            # Download the image
            image_response = requests.get(image_url)
            if image_response.status_code == 200:
                # Upload the image to WordPress media library
                media_headers = {
                    'Content-Disposition': f'attachment; filename="feature_image.jpg"',
                    'Content-Type': 'image/jpeg',
                }
                media_data = image_response.content
                media_response = requests.post(
                    f"{wordpress_url}/media",
                    data=media_data,
                    headers=media_headers,
                    auth=auth
                )
                if media_response.status_code == 201:
                    media_id = media_response.json().get("id")
                    post_data["featured_media"] = media_id
                else:
                    logging.error(f"Failed to upload feature image: {media_response.text}")
            else:
                logging.error(f"Failed to download image from URL: {image_url}")

        response = requests.post(f"{wordpress_url}/posts", json=post_data, auth=auth)
        if response.status_code == 201:
            logging.info(f"Blog post published to WordPress: {response.json().get('link')}")
            return response.json()
        else:
            logging.error(f"Failed to publish post: {response.text}")
            return None
    except Exception as e:
        logging.error(f"Exception in post_to_wordpress: {e}")
        return None

def generate_blog():
    """
    Orchestrates the blog generation process.
    """
    # Initialize FAISS index and metadata
    index = initialize_faiss()
    metadata = load_metadata()

    # Generate content
    content = generate_content()
    if not content:
        logging.error("Failed to generate content.")
        return None

    # Render template
    rendered_html = render_template(content)
    if not rendered_html:
        logging.error("Failed to render template.")
        return None

    # Save output
    post_title = content.get('post_title', 'blog_post')
    post_excerpt = content['introduction']['content'][:150]
    output_path = save_output(rendered_html, post_title)

    # Generate embeddings for the title and content
    texts = [post_title, content['introduction']['content']]
    embeddings = generate_embeddings(texts)
    if embeddings is not None:
        # Normalize embeddings
        faiss.normalize_L2(embeddings)
        # Add to FAISS index
        index.add(embeddings)
        faiss.write_index(index, VECTOR_DB_INDEX_FILE)
        # Update metadata
        metadata_entry = {
            "title": post_title,
            "excerpt": post_excerpt,
            "link": WORDPRESS_URL.replace('/wp-json/wp/v2', '') + f"/{post_title.replace(' ', '-').lower()}/",  # Simplistic link generation
            "timestamp": time.strftime("%Y-%m-%d %H:%M:%S")
        }
        metadata.append(metadata_entry)
        save_metadata(metadata)
    else:
        logging.error("Embeddings generation failed; skipping vector database entry.")

    # Post to WordPress
    try:
        wordpress_response = post_to_wordpress(
            title=post_title,
            content=rendered_html,
            excerpt=post_excerpt,
            categories=DEFAULT_CATEGORIES,
            tags=DEFAULT_TAGS,
            image_url=content['image']['url']
        )
        if wordpress_response:
            logging.info(f"Blog post published: {wordpress_response['link']}")
            print(f"Blog post published: {wordpress_response['link']}")
        else:
            logging.error("Failed to publish to WordPress.")
            print("Failed to publish to WordPress.")
    except Exception as e:
        logging.error(f"Failed to publish to WordPress: {e}")
        print(f"Failed to publish to WordPress: {e}")

    return output_path

def initialize_faiss():
    """
    Initializes or loads the FAISS index.
    """
    if os.path.exists(VECTOR_DB_INDEX_FILE):
        index = faiss.read_index(VECTOR_DB_INDEX_FILE)
        logging.info("FAISS index loaded successfully.")
    else:
        index = faiss.IndexFlatIP(VECTOR_DB_DIMENSION)  # Using Inner Product for cosine similarity
        faiss.write_index(index, VECTOR_DB_INDEX_FILE)
        logging.info("FAISS index initialized and saved.")
    return index

def load_metadata():
    """
    Loads the metadata associated with the vector database.
    """
    if os.path.exists(VECTOR_DB_METADATA_FILE):
        with open(VECTOR_DB_METADATA_FILE, 'r', encoding='utf-8') as f:
            metadata = json.load(f)
        logging.info("Metadata loaded successfully.")
    else:
        metadata = []
        with open(VECTOR_DB_METADATA_FILE, 'w', encoding='utf-8') as f:
            json.dump(metadata, f)
        logging.info("Metadata file initialized.")
    return metadata

def save_metadata(metadata):
    """
    Saves the metadata to a JSON file.
    """
    with open(VECTOR_DB_METADATA_FILE, 'w', encoding='utf-8') as f:
        json.dump(metadata, f, indent=2)
    logging.info("Metadata saved successfully.")
  