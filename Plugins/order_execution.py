import os
from dotenv import load_dotenv
from alpaca_trade_api.rest import REST
import time
import logging
import requests
import mysql.connector
from datetime import datetime

# Load environment variables from .env file
load_dotenv()

# Retrieve API credentials from environment variables
API_KEY = os.getenv("ALPACA_API_KEY")
SECRET_KEY = os.getenv("ALPACA_SECRET_KEY")
BASE_URL = os.getenv("ALPACA_BASE_URL")

# MySQL Database Credentials
DB_HOST = os.getenv("MYSQL_DB_HOST", "localhost")
DB_USER = os.getenv("MYSQL_DB_USER", "root")
DB_PASSWORD = os.getenv("MYSQL_DB_PASSWORD", "")
DB_NAME = os.getenv("MYSQL_DB_NAME", "trading_db")

# Initialize Alpaca API connection
api = REST(API_KEY, SECRET_KEY, BASE_URL)

# Set up logging
logging.basicConfig(filename="trade_log.txt", level=logging.INFO, format="%(asctime)s - %(message)s")

# Connect to MySQL
def get_db_connection():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASSWORD,
        database=DB_NAME
    )

def place_market_order(symbol, qty, side):
    """
    Place a market order using Alpaca API.

    Returns:
        str: Order ID if successful, or an error message.
    """
    try:
        order = api.submit_order(
            symbol=symbol,
            qty=qty,
            side=side,
            type='market',
            time_in_force='gtc'
        )
        order_id = order.id  # Extract order ID
        print(f"✅ Order placed successfully! Order ID: {order_id}")  # Debugging
        return order_id
    except Exception as e:
        print(f"❌ Error placing order: {str(e)}")  # Debugging
        return None

def send_trade_to_php(order):
    """Send completed trade to the PHP API"""
    url = "https://freerideinvestor.com/store_trade.php"
    headers = {"Content-Type": "application/json"}
    

    try:
        response = requests.post(url, json=order, headers=headers)
        response.raise_for_status()  # Raise an error for bad responses
        print("📡 Sending trade to website...")
        print(response.json())  # Debugging
        return response.json()
    except requests.exceptions.RequestException as e:
        print(f"❌ Error sending trade to PHP: {str(e)}")
        return {"error": str(e)}

def format_timestamp(timestamp):
    """Convert Alpaca's timestamp to a MySQL-compatible format."""
    if timestamp:
        try:
            # Remove 'Z' and nanoseconds, keep only YYYY-MM-DD HH:MM:SS
            return datetime.strptime(timestamp[:19], "%Y-%m-%dT%H:%M:%S").strftime("%Y-%m-%d %H:%M:%S")
        except ValueError as e:
            print(f"❌ Error formatting timestamp: {timestamp} - {e}")
            return None  # Return None if there's a formatting error
    return None

def save_trade_to_db(order):
    """Save completed trade to MySQL database with correctly formatted timestamps."""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()

        sql = """
        INSERT INTO trades (id, symbol, qty, filled_qty, filled_avg_price, order_type, side, time_in_force, status, created_at, filled_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
        filled_qty = VALUES(filled_qty), filled_avg_price = VALUES(filled_avg_price), status = VALUES(status), filled_at = VALUES(filled_at);
        """

        data = (
            order['id'], order['symbol'], int(order['qty']), int(order['filled_qty']),
            float(order['filled_avg_price']) if order['filled_avg_price'] else None,
            order['order_type'], order['side'], order['time_in_force'],
            order['status'], format_timestamp(order['created_at']), format_timestamp(order['filled_at'])
        )

        cursor.execute(sql, data)
        conn.commit()
        cursor.close()
        conn.close()
        print("✅ Trade saved to MySQL!")

    except Exception as e:
        print(f"❌ Error saving trade to database: {e}")

def check_order_status(order_id):
    """Track order status and store it in the database when filled."""
    if not order_id:
        print("❌ Error: Invalid order_id provided")
        return {"error": "Invalid order_id provided"}

    while True:
        try:
            order = api.get_order(order_id)._raw
            print(f"📌 Order Status: {order['status']}")

            if order['status'] in ['filled', 'canceled', 'failed']:
                save_trade_to_db(order)  # Save to MySQL
                return order  

            time.sleep(2)  # Wait 2 seconds before checking again

        except Exception as e:
            print(f"❌ Error fetching order status: {str(e)}")
            return {"error": str(e)}

def place_limit_order(symbol, qty, side, limit_price):
    """
    Place a limit order using Alpaca API.
    
    Args:
        symbol (str): The stock symbol to trade.
        qty (int): Number of shares.
        side (str): 'buy' or 'sell'.
        limit_price (float): The price at which to execute the order.

    Returns:
        dict: Order details.
    """
    try:
        order = api.submit_order(
            symbol=symbol,
            qty=qty,
            side=side,
            type='limit',
            time_in_force='gtc',
            limit_price=limit_price  # Specify limit price
        )
        return order._raw
    except Exception as e:
        return {"error": str(e)}

def place_order_with_logging(symbol, qty, side):
    try:
        order = api.submit_order(
            symbol=symbol,
            qty=qty,
            side=side,
            type='market',
            time_in_force='gtc'
        )
        logging.info(f"Order placed: {order._raw}")
        return order._raw
    except Exception as e:
        logging.error(f"Order failed: {str(e)}")
        return {"error": str(e)}

# Example Usage
if __name__ == "__main__":
    order_id = place_market_order('AAPL', 1, 'buy')
    
    if order_id:  # Ensure order_id is valid before checking status
        final_status = check_order_status(order_id)
        print(final_status)
    else:
        print("❌ Order placement failed.")

    result = place_limit_order('AAPL', 1, 'buy', 180.50)  # Buy AAPL if price reaches $180.50
    print(result)
