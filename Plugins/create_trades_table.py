import os
import mysql.connector
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# MySQL Connection Details
DB_HOST = os.getenv("MYSQL_DB_HOST", "localhost")
DB_USER = os.getenv("MYSQL_DB_USER", "root")
DB_PASSWORD = os.getenv("MYSQL_DB_PASSWORD", "")
DB_NAME = os.getenv("MYSQL_DB_NAME", "freerideinvest")  # Ensure this matches your database name

# Connect to MySQL and Create Table
def create_trades_table():
    try:
        conn = mysql.connector.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME
        )
        cursor = conn.cursor()

        # SQL Query to Create Table
        create_table_query = """
        CREATE TABLE IF NOT EXISTS trades (
            id VARCHAR(255) PRIMARY KEY,
            symbol VARCHAR(10),
            qty INT,
            filled_qty INT,
            filled_avg_price DECIMAL(10,2),
            order_type VARCHAR(10),
            side VARCHAR(10),
            time_in_force VARCHAR(10),
            status VARCHAR(20),
            created_at TIMESTAMP,
            filled_at TIMESTAMP NULL
        );
        """

        # Execute Query
        cursor.execute(create_table_query)
        conn.commit()

        print("✅ Table 'trades' created successfully (if not already exists).")

        cursor.close()
        conn.close()

    except mysql.connector.Error as e:
        print(f"❌ MySQL error: {e}")

# Run the function
if __name__ == "__main__":
    create_trades_table()
