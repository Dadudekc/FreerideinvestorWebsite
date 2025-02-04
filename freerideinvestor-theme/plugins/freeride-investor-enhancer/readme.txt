=== FreeRide Investor Showcase ===
Contributors: yourusername
Tags: stock, investor, sentiment analysis, chart, finance
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display real-time stock data and sentiment analysis to showcase FreeRide Investor's vision.

== Description ==

The **FreeRide Investor Showcase** plugin allows you to display real-time stock data and perform sentiment analysis on recent news headlines related to a specific stock. Users can enter a stock symbol to fetch and display the current stock price, price changes, and sentiment analysis results. Additionally, it visualizes stock performance using Chart.js.

== Installation ==

1. **Upload the Plugin:**
   - Upload the `freeride-investor-showcase` folder to the `/wp-content/plugins/` directory.

2. **Activate the Plugin:**
   - Go to **Plugins > Installed Plugins** in your WordPress admin dashboard.
   - Locate **FreeRide Investor Showcase** and click **Activate**.

3. **Define API Keys:**
   - Edit your `wp-config.php` file.
   - Add the following lines, replacing the placeholders with your actual API keys:

     ```php
     define('FR_FINNHUB_API_KEY', 'your_finnhub_api_key_here');
     define('FR_OPENAI_API_KEY', 'your_openai_api_key_here');
     ```

4. **Create the Showcase Page:**
   - Go to **Pages > Add New**.
   - Add a title (e.g., "Investor Showcase").
   - In the **Page Attributes** section, select **FreeRide Investor Showcase** as the template.
   - Click **Publish**.

== Usage ==

1. **Shortcode:**
   - You can embed the showcase anywhere using the `[freeride_investor_showcase symbol="AAPL"]` shortcode.
   - Replace `AAPL` with your desired stock symbol.

2. **Custom Page Template:**
   - For a dedicated showcase page, use the custom page template as described in the Installation section.

== Frequently Asked Questions ==

= How do I obtain API keys for Finnhub and OpenAI? =

- **Finnhub:** Visit [Finnhub.io](https://finnhub.io/), sign up for an account, and obtain your API key from the dashboard.
- **OpenAI:** Visit [OpenAI](https://openai.com/), sign up for an account, and generate your API key from the API section.

= Can I customize the appearance of the charts? =

Yes! You can modify the JavaScript and CSS files to customize the Chart.js appearance to match your site's design.

= How do I translate the plugin into another language? =

The plugin is ready for translation. Use tools like [Poedit](https://poedit.net/) to create `.po` and `.mo` files based on the `freeride-investor-showcase.pot` template located in the `languages/` folder.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
