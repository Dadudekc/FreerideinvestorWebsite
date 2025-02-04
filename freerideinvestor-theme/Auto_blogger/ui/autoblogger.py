# ui/autoblogger.py

import sys
from PyQt5.QtWidgets import (
    QApplication, QMainWindow, QPushButton, QTextEdit, QVBoxLayout, QWidget, QMessageBox,
    QProgressBar, QDialog, QLabel, QLineEdit, QFormLayout, QDialogButtonBox, QListWidget, QListWidgetItem
)
from PyQt5.QtCore import Qt, QThread, pyqtSignal, QUrl
from PyQt5.QtWebEngineWidgets import QWebEngineView
import os
import json
import configparser
import faiss
from sentence_transformers import SentenceTransformer
import numpy as np
import logging

# Import the generate_blog function from generate_blog.py
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from generate_blog import generate_blog, post_to_wordpress

class SettingsDialog(QDialog):
    def __init__(self, parent=None):
        super().__init__(parent)
        self.setWindowTitle("Settings")
        self.setup_ui()

    def setup_ui(self):
        layout = QFormLayout()
        self.category_input = QLineEdit()
        self.tag_input = QLineEdit()
        self.status_input = QLineEdit()

        layout.addRow("Categories (comma-separated):", self.category_input)
        layout.addRow("Tags (comma-separated):", self.tag_input)
        layout.addRow("Post Status ('draft' or 'publish'):", self.status_input)

        buttons = QDialogButtonBox(QDialogButtonBox.Ok | QDialogButtonBox.Cancel)
        buttons.accepted.connect(self.accept)
        buttons.rejected.connect(self.reject)

        layout.addWidget(buttons)
        self.setLayout(layout)

    def get_settings(self):
        return {
            'categories': self.category_input.text(),
            'tags': self.tag_input.text(),
            'status': self.status_input.text()
        }

class SearchDialog(QDialog):
    def __init__(self, faiss_index, metadata, parent=None):
        super().__init__(parent)
        self.setWindowTitle("Search Similar Blog Posts")
        self.faiss_index = faiss_index
        self.metadata = metadata
        self.setup_ui()

    def setup_ui(self):
        layout = QVBoxLayout()
        self.query_input = QLineEdit()
        self.query_input.setPlaceholderText("Enter search query...")
        self.search_button = QPushButton("Search")
        self.search_button.clicked.connect(self.perform_search)
        self.results_list = QListWidget()

        layout.addWidget(QLabel("Search Similar Blog Posts:"))
        layout.addWidget(self.query_input)
        layout.addWidget(self.search_button)
        layout.addWidget(self.results_list)

        self.setLayout(layout)

    def perform_search(self):
        query = self.query_input.text().strip()
        if not query:
            QMessageBox.warning(self, "Empty Query", "Please enter a search query.")
            return

        # Generate embedding for the query
        model = SentenceTransformer('all-MiniLM-L6-v2')
        query_embedding = model.encode([query], convert_to_numpy=True)
        faiss.normalize_L2(query_embedding)

        # Search FAISS index
        k = 5  # Number of nearest neighbors
        distances, indices = self.faiss_index.search(query_embedding, k)

        # Clear previous results
        self.results_list.clear()

        for distance, idx in zip(distances[0], indices[0]):
            if idx == -1:
                continue
            metadata = self.metadata[idx]
            item_text = f"Title: {metadata['title']}\nExcerpt: {metadata['excerpt']}\nLink: {metadata['link']}\n"
            item = QListWidgetItem(item_text)
            self.results_list.addItem(item)

class WorkerThread(QThread):
    """
    Worker thread for generating the blog post.
    """
    log_signal = pyqtSignal(str)
    progress_signal = pyqtSignal(int)
    finished_signal = pyqtSignal(str)

    def run(self):
        """
        Executes the blog generation process and emits logs.
        """
        try:
            self.log_signal.emit("Starting blog generation...")
            output_path = generate_blog()
            if output_path:
                self.log_signal.emit("Blog generation completed successfully.")
                self.finished_signal.emit(output_path)
            else:
                self.log_signal.emit("Blog generation failed. Check logs for details.")
                self.finished_signal.emit("")
        except Exception as e:
            self.log_signal.emit(f"An error occurred: {str(e)}")
            self.finished_signal.emit("")

class AutoBloggerApp(QMainWindow):
    def __init__(self):
        super().__init__()
        self.setWindowTitle("AutoBlogger")
        self.setGeometry(100, 100, 1200, 800)
        self.setup_ui()

        self.base_dir = os.path.dirname(os.path.abspath(__file__))
        self.output_dir = os.path.join(self.base_dir, 'output')
        self.latest_output_path = ""
        self.ensure_output_directory()
        self.load_vector_db()
        self.show()

        self.config = configparser.ConfigParser()
        self.config.read('config.ini')

    def ensure_output_directory(self):
        """
        Ensures the output directory exists.
        """
        if not os.path.exists(self.output_dir):
            os.makedirs(self.output_dir)

    def load_vector_db(self):
        """
        Loads the FAISS index and metadata.
        """
        try:
            from generate_blog import VECTOR_DB_DIMENSION, VECTOR_DB_INDEX_FILE, VECTOR_DB_METADATA_FILE
            if os.path.exists(VECTOR_DB_INDEX_FILE):
                self.index = faiss.read_index(VECTOR_DB_INDEX_FILE)
                logging.info("FAISS index loaded successfully.")
            else:
                self.index = faiss.IndexFlatIP(VECTOR_DB_DIMENSION)  # Inner Product for cosine similarity
                faiss.write_index(self.index, VECTOR_DB_INDEX_FILE)
                logging.info("FAISS index initialized and saved.")
            if os.path.exists(VECTOR_DB_METADATA_FILE):
                with open(VECTOR_DB_METADATA_FILE, 'r', encoding='utf-8') as f:
                    self.metadata = json.load(f)
                logging.info("Metadata loaded successfully.")
            else:
                self.metadata = []
                with open(VECTOR_DB_METADATA_FILE, 'w', encoding='utf-8') as f:
                    json.dump(self.metadata, f)
                logging.info("Metadata file initialized.")
        except Exception as e:
            logging.error(f"Error loading vector database: {e}")
            self.index = None
            self.metadata = []

    def setup_ui(self):
        """
        Sets up the GUI components.
        """
        central_widget = QWidget()
        self.setCentralWidget(central_widget)
        layout = QVBoxLayout()

        self.generate_button = QPushButton("Generate Blog Post")
        self.generate_button.setFixedHeight(40)
        self.generate_button.clicked.connect(self.start_generation)
        layout.addWidget(self.generate_button)

        self.view_output_button = QPushButton("View Output Folder")
        self.view_output_button.setFixedHeight(40)
        self.view_output_button.clicked.connect(self.open_output_folder)
        layout.addWidget(self.view_output_button)

        self.preview_button = QPushButton("Preview Blog Post")
        self.preview_button.setFixedHeight(40)
        self.preview_button.clicked.connect(self.preview_blog_post)
        self.preview_button.setEnabled(False)
        layout.addWidget(self.preview_button)

        self.post_button = QPushButton("Post to Blog")
        self.post_button.setFixedHeight(40)
        self.post_button.clicked.connect(self.post_to_blog)
        self.post_button.setEnabled(False)
        layout.addWidget(self.post_button)

        self.search_button = QPushButton("Search Similar Posts")
        self.search_button.setFixedHeight(40)
        self.search_button.clicked.connect(self.search_posts)
        layout.addWidget(self.search_button)

        self.settings_button = QPushButton("Settings")
        self.settings_button.setFixedHeight(40)
        self.settings_button.clicked.connect(self.open_settings)
        layout.addWidget(self.settings_button)

        self.progress_bar = QProgressBar()
        self.progress_bar.setValue(0)
        layout.addWidget(self.progress_bar)

        self.log_display = QTextEdit()
        self.log_display.setReadOnly(True)
        layout.addWidget(self.log_display)

        self.web_view = QWebEngineView()
        self.web_view.setVisible(False)
        layout.addWidget(self.web_view)

        central_widget.setLayout(layout)

    def start_generation(self):
        """
        Initiates the blog generation in a separate thread.
        """
        self.generate_button.setEnabled(False)
        self.preview_button.setEnabled(False)
        self.post_button.setEnabled(False)
        self.search_button.setEnabled(False)
        self.log_display.append("Initiating blog post generation...\n")
        self.progress_bar.setValue(0)

        self.worker = WorkerThread()
        self.worker.log_signal.connect(self.update_log)
        self.worker.progress_signal.connect(self.update_progress)
        self.worker.finished_signal.connect(self.generation_finished)
        self.worker.start()

    def update_log(self, message):
        """
        Updates the log display with new messages.
        """
        self.log_display.append(message)
        self.log_display.verticalScrollBar().setValue(self.log_display.verticalScrollBar().maximum())

    def update_progress(self, value):
        """
        Updates the progress bar.
        """
        self.progress_bar.setValue(value)

    def generation_finished(self, output_path):
        """
        Handles actions after blog generation is finished.
        """
        if output_path:
            self.latest_output_path = output_path
            self.log_display.append(f"Blog post saved at: {output_path}\n")
            QMessageBox.information(self, "Success", f"Blog post generated successfully:\n{output_path}")
            self.preview_button.setEnabled(True)
            self.post_button.setEnabled(True)
            self.search_button.setEnabled(True)
        else:
            self.log_display.append("Blog post generation failed. Check logs for details.\n")
            QMessageBox.warning(self, "Failure", "Failed to generate the blog post. Check logs for details.")
        self.generate_button.setEnabled(True)
        self.progress_bar.setValue(100)

    def open_output_folder(self):
        """
        Opens the output folder in the file explorer.
        """
        try:
            os.startfile(self.output_dir)  # For Windows
        except AttributeError:
            import subprocess, platform
            if platform.system() == "Darwin":
                subprocess.Popen(["open", self.output_dir])
            else:
                subprocess.Popen(["xdg-open", self.output_dir])

    def preview_blog_post(self):
        """
        Opens the latest generated blog post in the web view.
        """
        if not self.latest_output_path:
            QMessageBox.warning(self, "No Output", "No blog post has been generated yet.")
            return

        if not os.path.exists(self.latest_output_path):
            QMessageBox.warning(self, "File Not Found", "The latest blog post file does not exist.")
            return

        self.web_view.setVisible(True)
        file_url = QUrl.fromLocalFile(self.latest_output_path)
        self.web_view.setUrl(file_url)
        self.log_display.append(f"Previewing blog post: {self.latest_output_path}\n")
        self.web_view.page().runJavaScript("window.scrollTo(0, 0);")

    def post_to_blog(self):
        """
        Posts the latest generated blog to WordPress.
        """
        if not self.latest_output_path:
            QMessageBox.warning(self, "No Blog Post", "No blog post available to upload.")
            return

        try:
            with open(self.latest_output_path, 'r', encoding='utf-8') as f:
                content_html = f.read()

            # Load settings from config.ini
            post_title = self.config.get('post', 'title', fallback="Automated Blog Post")
            post_excerpt = content_html[:150]
            categories = json.loads(self.config.get('wordpress', 'categories', fallback='[]'))
            tags = json.loads(self.config.get('wordpress', 'tags', fallback='[]'))
            feature_image = "https://via.placeholder.com/800x400.png?text=Automation+Process"  # Customize if needed

            # Post to WordPress
            wordpress_response = post_to_wordpress(
                title=post_title,
                content=content_html,
                excerpt=post_excerpt,
                categories=categories,
                tags=tags,
                image_url=feature_image
            )

            if wordpress_response:
                QMessageBox.information(self, "Success", f"Blog posted: {wordpress_response['link']}")
                self.log_display.append(f"Blog posted to WordPress: {wordpress_response['link']}\n")
            else:
                QMessageBox.warning(self, "Error", "Failed to post blog. Check logs for details.")
                self.log_display.append("Failed to post blog to WordPress.\n")
        except Exception as e:
            QMessageBox.warning(self, "Error", f"Failed to post blog: {e}")
            self.log_display.append(f"Failed to post blog: {e}\n")

    def open_settings(self):
        """
        Opens the settings dialog.
        """
        dialog = SettingsDialog(self)
        if dialog.exec_() == QDialog.Accepted:
            settings = dialog.get_settings()
            self.config['wordpress'] = {
                'categories': json.dumps([cat.strip() for cat in settings['categories'].split(',')]),
                'tags': json.dumps([tag.strip() for tag in settings['tags'].split(',')]),
                'status': settings['status'] if settings['status'] in ['publish', 'draft'] else 'draft'
            }
            with open('config.ini', 'w') as configfile:
                self.config.write(configfile)
            QMessageBox.information(self, "Settings Saved", "Your settings have been saved.")
            self.load_vector_db()  # Reload vector DB in case categories/tags changed

    def search_posts(self):
        """
        Opens the search dialog to find similar blog posts.
        """
        if not self.index or not self.metadata:
            QMessageBox.warning(self, "Vector DB Not Ready", "The vector database is not initialized or empty.")
            return

        dialog = SearchDialog(self.index, self.metadata, self)
        dialog.exec_()

def main():
    app = QApplication(sys.argv)
    window = AutoBloggerApp()
    sys.exit(app.exec_())

if __name__ == "__main__":
    main()
