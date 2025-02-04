# Chain of Thought Showcase Plugin

## Overview

The **Chain of Thought Showcase** plugin integrates the `ChainOfThoughtReasoner` into your WordPress site, allowing users to input tasks, view reasoning steps, and visualize the underlying reasoning graph.

## Features

- **Interactive Task Submission:** Users can submit tasks directly from your site.
- **Reasoning Visualization:** Displays a dynamic reasoning graph using Streamlit.
- **Seamless Integration:** Easily embed the showcase using shortcodes.

## Installation

1. **Upload the Plugin:**
   - Upload the `chain_of_thought_showcase` folder to the `/wp-content/plugins/` directory.

2. **Activate the Plugin:**
   - Navigate to the **Plugins** section in your WordPress admin dashboard.
   - Locate **Chain of Thought Showcase** and click **Activate**.

3. **Set Up Backend and Frontend Services:**
   - Navigate to the plugin directory:
     ```bash
     cd wp-content/plugins/chain_of_thought_showcase/
     ```
   - **Backend Setup:**
     - Navigate to the `backend/` folder.
     - Create and activate a virtual environment:
       ```bash
       python3 -m venv venv
       source venv/bin/activate
       ```
     - Install dependencies:
       ```bash
       pip install -r requirements.txt
       ```
     - Start the FastAPI server:
       ```bash
       uvicorn main:app --host 0.0.0.0 --port 8000
       ```
   - **Frontend Setup:**
     - Open a new terminal and navigate to the `frontend/` folder.
     - Create and activate a virtual environment:
       ```bash
       python3 -m venv venv
       source venv/bin/activate
       ```
     - Install dependencies:
       ```bash
       pip install -r requirements.txt
       ```
     - Start the Streamlit app:
       ```bash
       streamlit run app.py
       ```

4. **Embed the Showcase:**
   - Use the shortcode `[chain_of_thought_showcase]` in any page or post where you want the showcase to appear.

## Usage

1. **Navigate to the Page:**
   - Visit the page or post where you embedded the shortcode.

2. **Submit a Task:**
   - Enter a task description in the provided form and submit.

3. **View Results:**
   - The final result and the reasoning graph will be displayed below the submission form.

## Customization

- **Styling:**
  - Modify `assets/css/styles.css` to customize the appearance of the showcase.

- **Scripts:**
  - Add or modify JavaScript in `assets/js/scripts.js` for additional interactivity.

## Troubleshooting

- **Backend Server Issues:**
  - Ensure the FastAPI server is running and accessible at the specified URL (`http://localhost:8000`).

- **Frontend App Issues:**
  - Ensure the Streamlit app is running and accessible at the specified URL (`http://localhost:8501`).

- **Shortcode Not Displaying:**
  - Verify that the plugin is activated and the shortcode is correctly placed in the page/post.

## Contributing

Contributions are welcome! Please open an issue or submit a pull request for any enhancements or bug fixes.

## License

[MIT License](LICENSE)

