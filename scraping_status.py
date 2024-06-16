import requests
import pandas as pd
import markdown
from bs4 import BeautifulSoup

# 指定されたURLからMarkdownのテキストを取得する
def fetch_markdown(url):
    try:
        response = requests.get(url)
        response.raise_for_status()
        return response.text
    except requests.exceptions.RequestException as e:
        print(f"Error fetching the markdown file: {e}")
        return None

# MarkdownのテキストをHTMLに変換する
def convert_markdown_to_html(markdown_content):
    try:
        return markdown.markdown(markdown_content)
    except Exception as e:
        print(f"Error converting markdown to HTML: {e}")
        return None

def extract_tables_from_html(html):
    try:
        soup = BeautifulSoup(html, 'html.parser')

        # スクレイピングの開始位置
        start = soup.find('h3', string='Get device status')
        if start is None:
            print("Start h3 tag 'Get device status' not found.")
            return []

        # 開始位置からすべてのテーブルを抽出
        tables = start.find_all_next('table')

        if not tables:
            print("No tables found in the HTML content.")
        return tables
    except Exception as e:
        print(f"Error parsing HTML with BeautifulSoup: {e}")
        return []


# テーブルのリストから1列目のheaderが"Key", 2列目が"Value Type"であるテーブルを取得する
def extract_relevant_tables(tables):
    relevant_tables = []
    print(tables)
    for table in tables:
        rows = []
        for tr in table.find_all('tr'):
            cells = tr.find_all(['th', 'td'])
            row = [cell.get_text(strip=True) for cell in cells]
            rows.append(row)
        
        # ヘッダの1列目が"Key"で、2列目が"Key Name"でないことを確認
        if rows and len(rows[0]) > 1 and rows[0][0] == "Key" and rows[0][1] == "Value Type":
            relevant_tables.append(rows)
    
    return relevant_tables

# pandasを使ってテーブルをCSVに変換する
def convert_tables_to_csv(tables, output_file):
    try:
        dfs = []  # データフレームのリストを初期化
        for table in tables:
            df = pd.DataFrame(table[1:], columns=table[0])  # 各テーブルをデータフレームに変換
            dfs.append(df)  # データフレームをリストに追加
        
        if dfs:
            df = pd.concat(dfs, ignore_index=True)  # データフレームを結合
            df.to_csv(output_file, index=False)  # CSVファイルに出力
            print(f"{output_file} has been created successfully.")
        else:
            print("No relevant tables found to write to the CSV file.")
    except Exception as e:
        print(f"Error converting tables to CSV: {e}")

def main():
    url = "https://github.com/OpenWonderLabs/SwitchBotAPI/blob/main/README.md"
    markdown_content = fetch_markdown(url)
    if markdown_content is None:
        return

    html = convert_markdown_to_html(markdown_content)
    if html is None:
        return

    tables = extract_tables_from_html(html)
    if not tables:
        return

    relevant_tables = extract_relevant_tables(tables)
    if not relevant_tables:
        return

    convert_tables_to_csv(relevant_tables, 'output_status.csv')

if __name__ == "__main__":
    main()
