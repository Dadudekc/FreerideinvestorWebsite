#!/usr/bin/env python3
"""
process_pdf.py
--------------
Extracts trade data from PDF files using pdfplumber and regex, then outputs JSON.
"""

import sys
import re
import json
import logging

# Attempt to import pdfplumber
try:
    import pdfplumber
except ImportError:
    logging.critical("Error: 'pdfplumber' module not found. Install with 'pip install pdfplumber'.")
    sys.exit(1)

# Logging configuration
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[logging.StreamHandler()]
)

# Regex pattern for extracting trade data
TRADE_PATTERN = re.compile(
    r'(?P<Symbol>\w+)\s+'
    r'(?P<TradeDate>\d{2}/\d{2}/\d{4})\s+'
    r'(?P<OptionType>Call|Put)\s+'
    r'\$?(?P<StrikePrice>\d+\.\d{2})\s+'
    r'(?P<SymbolRepeat>\w+)\s+'
    r'(?P<AccountType>Margin|Cash)\s+'
    r'(?P<TransactionType>BTO|STC|BTC|STO)\s+'
    r'(?P<ExecutionDate>\d{2}/\d{2}/\d{4})\s+'
    r'(?P<Quantity>\d+)\s+'
    r'\$?(?P<PricePerContract>\d+\.\d{2})\s+'
    r'\$?(?P<NetAmount>-?\d{1,3}(?:,\d{3})*(?:\.\d{2})?)'
)

def extract_trades_from_pdf(file_path):
    trades = []
    try:
        with pdfplumber.open(file_path) as pdf:
            for page_number, page in enumerate(pdf.pages, start=1):
                text = page.extract_text()
                if not text:
                    logging.warning(f"Page {page_number} has no text.")
                    continue

                for line_number, line in enumerate(text.split('\n'), start=1):
                    match = TRADE_PATTERN.search(line)
                    if match:
                        trade = match.groupdict()
                        try:
                            trade['NetAmount'] = float(trade['NetAmount'].replace(',', ''))
                            trades.append(trade)
                        except ValueError as ve:
                            logging.error(f"Invalid NetAmount on page {page_number}, line {line_number}: {line}")
    except Exception as e:
        logging.error(f"Error processing PDF '{file_path}': {e}")

    logging.info(f"✅ Extracted {len(trades)} trades from PDF.")
    return trades

if __name__ == "__main__":
    if len(sys.argv) != 2:
        logging.error("Usage: python process_pdf.py <pdf_file_path>")
        sys.exit(1)

    file_path = sys.argv[1]
    extracted_trades = extract_trades_from_pdf(file_path)

    # Output JSON for WordPress integration
    try:
        print(json.dumps(extracted_trades, indent=4))
    except (TypeError, ValueError) as json_error:
        logging.error(f"JSON output error: {json_error}")
        sys.exit(1)
