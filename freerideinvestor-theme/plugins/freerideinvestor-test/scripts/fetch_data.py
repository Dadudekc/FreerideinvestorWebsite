#!/usr/bin/env python3
import os
import sys
import json
import requests
from datetime import datetime, timedelta

def fetch_stock_data(symbols, alpha_vantage_api_key, finnhub_api_key, news_api_key):
    data = {}
    for symbol in symbols.split(','):
        symbol = symbol.strip().upper()
        if not symbol:
            continue

        data[symbol] = {}
        
        # Fetch latest price from Alpha Vantage
        try:
            av_response = requests.get(
                'https://www.alphavantage.co/query',
                params={
                    'function': 'GLOBAL_QUOTE',
                    'symbol': symbol,
                    'apikey': alpha_vantage_api_key
                },
                timeout=10
            )
            av_data = av_response.json()
            if 'Global Quote' in av_data and '05. price' in av_data['Global Quote']:
                latest_price = float(av_data['Global Quote']['05. price'])
                data[symbol]['latest_price'] = latest_price
            else:
                data[symbol]['latest_price'] = None
                data[symbol]['error'] = 'Alpha Vantage: No price data found.'
        except Exception as e:
            data[symbol]['latest_price'] = None
            data[symbol]['error'] = f'Alpha Vantage: {str(e)}'

        # Fetch historical data from Finnhub
        try:
            # Define the time range for historical data (last 30 days)
            end_date = datetime.utcnow()
            start_date = end_date - timedelta(days=30)
            finnhub_response = requests.get(
                'https://finnhub.io/api/v1/stock/candle',
                params={
                    'symbol': symbol,
                    'resolution': 'D',
                    'from': int(start_date.timestamp()),
                    'to': int(end_date.timestamp()),
                },
                headers={
                    'X-Finnhub-Token': finnhub_api_key
                },
                timeout=10
            )
            finnhub_data = finnhub_response.json()
            if finnhub_data.get('s') == 'ok':
                historical = []
                for ts, close in zip(finnhub_data['t'], finnhub_data['c']):
                    date = datetime.utcfromtimestamp(ts).strftime('%Y-%m-%d')
                    historical.append({'date': date, 'price': close})
                data[symbol]['historical'] = historical
            else:
                data[symbol]['historical'] = []
                data[symbol]['error'] = 'Finnhub: No historical data found.'
        except Exception as e:
            data[symbol]['historical'] = []
            data[symbol]['error'] = f'Finnhub: {str(e)}'

        # Fetch news from News API
        try:
            news_response = requests.get(
                'https://newsapi.org/v2/everything',
                params={
                    'q': symbol,
                    'apiKey': news_api_key,
                    'pageSize': 5,
                    'sortBy': 'publishedAt',
                    'language': 'en',
                },
                timeout=10
            )
            news_data = news_response.json()
            if news_data.get('status') == 'ok':
                news = []
                for article in news_data.get('articles', []):
                    news.append({
                        'headline': article.get('title', ''),
                        'summary': article.get('description', ''),
                        'url': article.get('url', ''),
                        'datetime': article.get('publishedAt', '')
                    })
                data[symbol]['news'] = news
            else:
                data[symbol]['news'] = []
                data[symbol]['error'] = 'News API: No news data found.'
        except Exception as e:
            data[symbol]['news'] = []
            data[symbol]['error'] = f'News API: {str(e)}'

    return data

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(json.dumps({"error": "No symbols provided."}))
        sys.exit(1)

    symbols_str = sys.argv[1]
    alpha_vantage_api_key = os.getenv('ALPHA_VANTAGE_API_KEY')
    finnhub_api_key = os.getenv('FINNHUB_API_KEY')
    news_api_key = os.getenv('NEWS_API_KEY')

    if not all([alpha_vantage_api_key, finnhub_api_key, news_api_key]):
        print(json.dumps({"error": "Missing one or more API keys."}))
        sys.exit(1)

    data = fetch_stock_data(symbols_str, alpha_vantage_api_key, finnhub_api_key, news_api_key)
    print(json.dumps(data))
