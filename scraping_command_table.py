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

# HTMLからテーブルを抽出する
def extract_tables_from_html(html):
    try:
        soup = BeautifulSoup(html, 'html.parser')
        tables = soup.find_all('table')
        if not tables:
            print("No tables found in the HTML content.")
        return tables
    except Exception as e:
        print(f"Error parsing HTML with BeautifulSoup: {e}")
        return []


# テーブルのリストから2列目のheaderが"commandType"であるテーブルを取得する
def extract_relevant_tables(tables):
    relevant_tables = []
    for table in tables:
        rows = []
        for tr in table.find_all('tr'):
            cells = tr.find_all(['th', 'td'])
            row = [cell.get_text(strip=True) for cell in cells]
            rows.append(row)
        
        if rows and len(rows[0]) > 1 and rows[0][1] == "commandType":
            relevant_tables.append(rows)
    
    return relevant_tables

# pandasを使ってテーブルをCSVに変換する
# pandasのDataFrameを使ってテーブルを作成し、to_csv()メソッドを使ってCSVファイルに書き込む
def convert_tables_to_csv(tables, output_file):
    try:
        all_rows = []
        for table in tables:
            all_rows.extend(table)
        
        if all_rows:
            df = pd.DataFrame(all_rows[1:], columns=all_rows[0])
            df.to_csv(output_file, index=False)
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

    convert_tables_to_csv(relevant_tables, 'output.csv')

if __name__ == "__main__":
    main()
