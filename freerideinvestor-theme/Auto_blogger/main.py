# main.py
import sys
import subprocess
import shlex
import os
import time
import json
import markdown  # Optional
from PyQt5.QtWidgets import (
    QApplication, QWidget, QVBoxLayout, QHBoxLayout, QLabel,
    QLineEdit, QPushButton, QTextEdit, QFileDialog, QMessageBox, QProgressBar
)
from PyQt5.QtCore import Qt, QThread, pyqtSignal
from jinja2 import Environment, FileSystemLoader
import logging

# Configure logging
logging.basicConfig(
    filename='autoblogger.log',
    level=logging.INFO,
    format='%(asctime)s:%(levelname)s:%(message)s'
)

class BlogGeneratorThread(QThread):
    # Define custom signals
    progress = pyqtSignal(int)
    log = pyqtSignal(str)
    finished_signal = pyqtSignal(str)  # Emits path to generated file

    def __init__(self, prompts, template_path, output_dir):
        super().__init__()
        self.prompts = prompts
        self.template_path = template_path
        self.output_dir = output_dir

    def run(self):
        try:
            content = self.generate_content()
            self.progress.emit(50)
            rendered_html = self.render_template(content)
            self.progress.emit(80)
            output_path = self.save_output(rendered_html, content.get('post_title', 'blog_post'))
            self.progress.emit(100)
            self.finished_signal.emit(output_path)
        except Exception as e:
            logging.error(f"Error in BlogGeneratorThread: {e}")
            self.log.emit(f"Error: {e}")

    def run_ollama(self, prompt):
        try:
            command = f'ollama run mistral:latest "{prompt}" --stdout'
            logging.info(f'Running command: {command}')
            args = shlex.split(command)
            result = subprocess.run(args, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True, check=True)
            response = result.stdout.strip()
            logging.info(f'Ollama response: {response}')
            return response
        except subprocess.CalledProcessError as e:
            logging.error(f"Error running Ollama: {e.stderr}")
            self.log.emit(f"Error running Ollama: {e.stderr}")
            return ""

    def convert_markdown_to_html(self, text):
        return markdown.markdown(text)

    def generate_content(self):
        content = {}

        # Define prompts
        prompts = self.prompts

        # Generate post title
        content['post_title'] = self.run_ollama(prompts['post_title'])
        if not content['post_title']:
            content['post_title'] = "Automating Blog Posts with AI: A Comprehensive Guide"

        # Generate post subtitle
        content['post_subtitle'] = self.run_ollama(prompts['post_subtitle'])
        if not content['post_subtitle']:
            content['post_subtitle'] = "Leveraging AI to Streamline Your Blogging Workflow"

        # Generate introduction
        introduction_text = self.run_ollama(prompts['introduction'])
        if not introduction_text:
            introduction_text = "Welcome to this comprehensive guide on automating your blog posts using AI. In today's digital age, leveraging artificial intelligence can significantly streamline your content creation process, saving you time and enhancing productivity."

        content['introduction'] = {
            "title": "Introduction",
            "content": introduction_text
        }

        # Generate main sections
        content['sections'] = []
        for section in prompts['sections']:
            section_title = section['title']
            section_content = self.run_ollama(section['prompt'])
            if not section_content:
                section_content = f"Content for '{section_title}' goes here."
            content['sections'].append({
                "title": section_title,
                "content": section_content
            })
            time.sleep(1)  # Prevent overwhelming the AI

        # Generate image description
        image_description = self.run_ollama(prompts['image']['prompt'])
        if not image_description:
            image_description = "An illustrative diagram showing the flow of automating blog post creation using AI models like Mistral."

        content['image'] = {
            "title": prompts['image']['title'],
            "url": "https://via.placeholder.com/800x400.png?text=Automation+Process",  # Placeholder
            "alt": image_description
        }

        # Generate conclusion
        conclusion_text = self.run_ollama(prompts['conclusion'])
        if not conclusion_text:
            conclusion_text = "In conclusion, automating your blog posts with AI can revolutionize your content creation process. By integrating powerful models like Mistral, you can produce high-quality content efficiently, allowing you to focus more on strategy and less on repetitive tasks."

        content['conclusion'] = {
            "title": "Conclusion",
            "content": conclusion_text
        }

        # CTA remains static or can be dynamically generated
        content['cta'] = prompts['cta']

        return content

    def render_template(self, content):
        try:
            env = Environment(loader=FileSystemLoader(os.path.dirname(self.template_path)))
            template = env.get_template(os.path.basename(self.template_path))
            rendered_html = template.render(content)
            logging.info("Template rendered successfully.")
            return rendered_html
        except Exception as e:
            logging.error(f"Error rendering template: {e}")
            self.log.emit(f"Error rendering template: {e}")
            return ""

    def save_output(self, rendered_html, post_title):
        try:
            # Create output directory if it doesn't exist
            os.makedirs(self.output_dir, exist_ok=True)

            # Create a safe filename
            safe_title = ''.join(c for c in post_title if c.isalnum() or c in (' ', '_')).rstrip()
            filename = f"{safe_title.strip().replace(' ', '_').lower()}.html"
            output_path = os.path.join(self.output_dir, filename)

            with open(output_path, 'w', encoding='utf-8') as f:
                f.write(rendered_html)
            logging.info(f"Blog post generated successfully: {output_path}")
            return output_path
        except Exception as e:
            logging.error(f"Error saving output file: {e}")
            self.log.emit(f"Error saving output file: {e}")
            return ""

class AutobloggerApp(QWidget):
    def __init__(self):
        super().__init__()
        self.template_path = "ui/blog_template.html"  # Set the correct path to your template
        self.output_dir = "ui/blog_posts"  # Set the correct output directory
        self.init_ui()


    def init_ui(self):
        # Set window properties
        self.setWindowTitle('AutoBlogger')
        self.setGeometry(100, 100, 800, 600)

        # Create layout
        main_layout = QVBoxLayout()

        # Title Input
        title_layout = QHBoxLayout()
        title_label = QLabel('Post Title:')
        self.title_input = QLineEdit()
        title_layout.addWidget(title_label)
        title_layout.addWidget(self.title_input)
        main_layout.addLayout(title_layout)

        # Subtitle Input
        subtitle_layout = QHBoxLayout()
        subtitle_label = QLabel('Post Subtitle:')
        self.subtitle_input = QLineEdit()
        subtitle_layout.addWidget(subtitle_label)
        subtitle_layout.addWidget(self.subtitle_input)
        main_layout.addLayout(subtitle_layout)

        # Generate Button
        self.generate_button = QPushButton('Generate Blog Post')
        self.generate_button.clicked.connect(self.generate_blog_post)
        main_layout.addWidget(self.generate_button)

        # Progress Bar
        self.progress_bar = QProgressBar()
        self.progress_bar.setValue(0)
        main_layout.addWidget(self.progress_bar)

        # Preview Area
        preview_label = QLabel('Blog Post Preview:')
        self.preview_area = QTextEdit()
        self.preview_area.setReadOnly(True)
        main_layout.addWidget(preview_label)
        main_layout.addWidget(self.preview_area)

        # Save Button
        self.save_button = QPushButton('Save Blog Post')
        self.save_button.clicked.connect(self.save_blog_post)
        self.save_button.setEnabled(False)
        main_layout.addWidget(self.save_button)

        # Log Area
        log_label = QLabel('Logs:')
        self.log_area = QTextEdit()
        self.log_area.setReadOnly(True)
        main_layout.addWidget(log_label)
        main_layout.addWidget(self.log_area)

        self.setLayout(main_layout)

    def generate_blog_post(self):
        # Disable the generate button to prevent multiple clicks
        self.generate_button.setEnabled(False)
        self.save_button.setEnabled(False)
        self.preview_area.clear()
        self.log_area.clear()
        self.progress_bar.setValue(0)

        # Define prompts
        prompts = {
            "post_title": "Generate an engaging blog post title about automating blog posts with AI.",
            "post_subtitle": "Generate a subtitle for the blog post titled 'Automating Blog Posts with AI'.",
            "introduction": "Write an introduction section for a blog post about automating blog posts using AI. The introduction should be engaging and set the stage for the rest of the post.",
            "sections": [
                {
                    "title": "Section Title",
                    "prompt": "Section prompt"
                }
            ],
            "image": {
                "title": "Image Title",
                "prompt": "Image prompt"
            },
            "conclusion": "Write a conclusion for the blog post about automating blog posts using AI.",
            "cta": "Call to Action"
        }

        # Generate blog post
        self.blog_generator_thread = BlogGeneratorThread(prompts, self.template_path, self.output_dir)
        self.blog_generator_thread.progress.connect(self.update_progress)
        self.blog_generator_thread.log.connect(self.update_log)
        self.blog_generator_thread.finished_signal.connect(self.handle_finished)
        self.blog_generator_thread.start()

    def update_progress(self, value):
        self.progress_bar.setValue(value)

    def update_log(self, message):
        self.log_area.append(message)

    def handle_finished(self, output_path):
        self.preview_area.setText(output_path)
        self.save_button.setEnabled(True)

    def save_blog_post(self):
        # Implement saving the blog post
        pass

if __name__ == "__main__":
    app = QApplication(sys.argv)
    window = AutobloggerApp()
    window.show()
    sys.exit(app.exec_())
