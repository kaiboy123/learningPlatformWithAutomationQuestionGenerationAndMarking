import sys
import json
import openai

# Set your OpenAI API key
openai.api_key = "sk-proj-xxx"


def evaluate_submission(input_data):
    """
    Evaluates the learner's submission using the OpenAI API.
    """
    try:
        user_code = input_data.get("userCode", "No code provided")
        question_title = input_data.get("questionTitle", "Unknown Title")
        question_content = input_data.get("questionContent", "Unknown Content")

        # Construct the AI prompt
        prompt = (
            f"You are an intelligent assistant for analyzing code submissions. "
            f"The user has attempted the following practical question.\n\n"
            f"Question Title: {question_title}\n"
            f"Question Content: {question_content}\n\n"
            f"User's Submission:\n{user_code}\n\n"
            "Your task is:\n"
            "1. Determine if the user's submission satisfies the requirements outlined in the question.\n"
            "2. If correct, respond with: {\"correct\": true}\n"
            "3. If incorrect, respond with: {\"correct\": false, \"feedback\": \"<constructive feedback on what's missing or incorrect>\"}"
        )

        # Call OpenAI API
        response = openai.ChatCompletion.create(
            model="gpt-3.5-turbo",
            max_tokens=500,
            messages=[
                {"role": "system", "content": "You are an evaluator for code submissions in an online learning platform."},
                {"role": "user", "content": prompt}
            ]
        )

        # Extract the content and parse as JSON
        raw_content = response["choices"][0]["message"]["content"]
        result = json.loads(raw_content)

        return result

    except Exception as e:
        # Handle errors gracefully
        return {"correct": False, "feedback": f"Error during processing: {str(e)}"}


def main():
    """
    Main function to handle file input and output.
    """
    if len(sys.argv) < 3:
        print("Usage: python analyze.py <input_json_path> <output_json_path>")
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        # Load the input JSON file
        with open(input_path, "r") as f:
            input_data = json.load(f)

        # Evaluate the submission
        result = evaluate_submission(input_data)

        # Save the result to the output file
        with open(output_path, "w") as f:
            json.dump(result, f, indent=4)

        print(f"Results saved to {output_path}")

    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)


if __name__ == "__main__":
    main()
