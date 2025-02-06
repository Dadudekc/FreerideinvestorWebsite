#!/usr/bin/env python3
"""
Purpose: Extract trade data from a PDF file and output the results as JSON.
"""

import sys
import pdfplumber
import re
import json

def main():
    # Ensure a file path argument is provided.
    if len(sys.argv) < 2:
        print("Usage: {} <pdf_file_path>".format(sys.argv[0]), file=sys.stderr)
        sys.exit(1)
    
    file_path = sys.argv[1]
    trades = []
    
    try:
        # Open and process the PDF file.
        with pdfplumber.open(file_path) as pdf:
            for page in pdf.pages:
                text = page.extract_text()
                if text:
                    # Process each line in the extracted text.
                    for line in text.split('\n'):
                        # Adjust the regex pattern as needed for your specific PDF format.
                        match = re.match(
                            r'(TSLA)\s+(\d{2}/\d{2}/\d{4})\s+(Call|Put)\s+\$(\d+\.\d+).*?(BTO|STC).*?(\d{2}/\d{2}/\d{4}).*?(\d+).*?\$(\d+\.\d+).*?\$(\d+\.\d+)',
                            line
                        )
                        if match:
                            trades.append({
                                "Expiration Date": match.group(2),
                                "Option Type": match.group(3),
                                "Strike Price": float(match.group(4)),
                                "Transaction Type": match.group(5),
                                "Trade Date": match.group(6),
                                "Quantity": int(match.group(7)),
                                "Price per Contract": float(match.group(8)),
                                "Total Cost": float(match.group(9))
                            })
    except Exception as e:
        # Log error to stderr (and optionally to a log file) and exit.
        print("Error processing PDF file: {}".format(e), file=sys.stderr)
        sys.exit(1)
    
    # Output the extracted trade data as JSON.
    # This will output "[]" if no trades were found.
    output = json.dumps(trades)
    print(output)
    sys.stdout.flush()

if __name__ == "__main__":
    main()
