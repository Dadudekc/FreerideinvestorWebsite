import requests
import os
import re
from urllib.parse import urljoin

def sanitize_filename(title):
    """
    Sanitize the post title to create a valid filename.
    Removes or replaces characters that are invalid in filenames.
    """
    # Remove any character that is not alphanumeric, space, hyphen, or underscore
    sanitized = re.sub(r'[^\w\s-]', '', title)
    # Replace spaces and hyphens with underscores
    sanitized = re.sub(r'[\s-]+', '_', sanitized)
    return sanitized

def fetch_all_posts(api_url, per_page=100):
    """
    Fetch all posts from the WordPress REST API handling pagination.
    """
    all_posts = []
    page = 1

    while True:
        params = {
            'per_page': per_page,
            'page': page
        }
        print(f"Fetching page {page}...")
        response = requests.get(api_url, params=params)

        if response.status_code == 200:
            posts = response.json()
            if not posts:
                print("No more posts found.")
                break
            all_posts.extend(posts)

            # Check if we've reached the last page
            total_pages = int(response.headers.get('X-WP-TotalPages', 1))
            if page >= total_pages:
                break
            page += 1
        else:
            print(f"Failed to fetch posts on page {page}: {response.status_code}")
            print(f"Response content: {response.text}")
            break

    return all_posts

def save_posts(posts, directory="website_posts"):
    """
    Save each post's content to an HTML file in the specified directory.
    """
    os.makedirs(directory, exist_ok=True)
    for post in posts:
        title = post.get('title', {}).get('rendered', 'untitled')
        content = post.get('content', {}).get('rendered', '')
        sanitized_title = sanitize_filename(title)
        filename = os.path.join(directory, f"{sanitized_title}.html")

        try:
            with open(filename, "w", encoding="utf-8") as file:
                file.write(content)
            print(f"Saved: {filename}")
        except Exception as e:
            print(f"Failed to save {filename}: {e}")

def main():
    # Base URL of your website's API
    base_url = "https://freerideinvestor.com"
    api_endpoint = "/wp-json/wp/v2/posts"
    api_url = urljoin(base_url, api_endpoint)

    print("Starting to fetch all posts...")
    posts = fetch_all_posts(api_url)
    print(f"Total posts fetched: {len(posts)}")

    if posts:
        print("Saving posts to files...")
        save_posts(posts)
        print("All posts saved successfully!")
    else:
        print("No posts to save.")

if __name__ == "__main__":
    main()
