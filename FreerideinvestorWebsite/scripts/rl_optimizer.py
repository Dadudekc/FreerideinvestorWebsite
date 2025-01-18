import sys
import json

def optimize_portfolio(data):
    portfolio = data['portfolio']
    market_data = data['market_data']

    # Example: Simple even distribution optimization
    total_symbols = len(portfolio)
    new_allocations = {item['symbol']: 1 / total_symbols for item in portfolio}

    return new_allocations

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(json.dumps({"error": "Invalid number of arguments."}))
        sys.exit(1)

    input_file = sys.argv[1]
    try:
        with open(input_file, 'r') as f:
            data = json.load(f)

        allocations = optimize_portfolio(data)
        print(json.dumps(allocations))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
