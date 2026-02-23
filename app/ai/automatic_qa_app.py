import sys
import json
import argparse
import openai
from langchain_community.document_loaders import PyPDFLoader

openai.api_key = "sk-proj-xxx"

def extract_pdf_content(file_path):
    loader = PyPDFLoader(file_path)
    docs = loader.load()
    return "".join([page.page_content for page in docs])

def create_single_qa(context, difficulty, question_type="open-ended"):
    if question_type == "multiple-choice":
        q_a_prompt = (
            f"Based on the provided text, create a single {difficulty} difficulty multiple-choice question:\n\n"
            f"{context}\n\n"
            f"The output should strictly follow this JSON format:\n"
            f"{{\n"
            f"  \"question\": \"<The multiple-choice question>\",\n"
            f"  \"options\": {{\n"
            f"    \"A\": \"<Option A>\",\n"
            f"    \"B\": \"<Option B>\",\n"
            f"    \"C\": \"<Option C>\",\n"
            f"    \"D\": \"<Option D>\"\n"
            f"  }},\n"
            f"  \"answer\": \"<The correct option (A, B, C, or D)>\"\n"
            f"}}"
        )
    else:
        q_a_prompt = (
            f"Based on the provided text, create a single {difficulty} difficulty open-ended question:\n\n"
            f"{context}\n\n"
            f"The output should strictly follow this JSON format:\n"
            f"{{\n"
            f"  \"question\": \"<The open-ended question>\",\n"
            f"  \"content\": \"<A summary or hint related to the question>\",\n"
            f"  \"answer\": \"<The correct answer to the question>\"\n"
            f"}}"
        )

    response = openai.ChatCompletion.create(
        model="gpt-3.5-turbo",
        max_tokens=500,
        messages=[
            {"role": "system", "content": "You are a helpful assistant that generates questions based on the provided text."},
            {"role": "user", "content": q_a_prompt}
        ]
    )
    raw_content = response["choices"][0]["message"]["content"]

    # Strip Markdown and ensure valid JSON
    if raw_content.startswith("```json"):
        raw_content = raw_content.strip("```json").strip("```").strip()

    return json.loads(raw_content)

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--file", help="Path to the input PDF file.")
    parser.add_argument("--content", help="Text content for generating questions.")
    parser.add_argument("--difficulty", help="Difficulty level of the questions.", default="normal")
    parser.add_argument("--type", help="Type of question: open-ended or multiple-choice.", default="open-ended")
    args = parser.parse_args()

    # Extract context based on input type
    if args.file:
        try:
            context = extract_pdf_content(args.file)
        except Exception as e:
            print(json.dumps({"error": f"Failed to process PDF file: {str(e)}"}))
            sys.exit(1)
    elif args.content:
        context = args.content.strip()
    else:
        print(json.dumps({"error": "No input provided."}))
        sys.exit(1)

    try:
        question = create_single_qa(context, args.difficulty.lower(), args.type.lower())
        print(json.dumps(question))
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)
