import sys
import json
import openai

# Set your OpenAI API key
openai.api_key = "sk-proj-xxx"

def mark_questions(submitted_answers):
    """
    Processes each question and evaluates the learner's answers using OpenAI API.
    """
    results = []
    for item in submitted_answers:
        question_id = item.get("questionID", "Unknown ID")
        question_title = item.get("questionTitle", "Unknown Title")
        correct_answer = item.get("correctAnswer", "Unknown Answer")
        learner_answer = item.get("learnerAnswer", "No Answer Provided")

        # Construct the AI prompt
        prompt = (
            f"Question ID: {question_id}\n"
            f"Question: {question_title}\n"
            f"Correct Answer: {correct_answer}\n"
            f"Learner's Answer: {learner_answer}\n\n"
            "Evaluate the learner's answer and provide:\n"
            "- A score (1 if correct, 0 if incorrect)\n"
            "- Feedback explaining the result.\n\n"
            "Respond in JSON format with keys 'score' and 'feedback'."
        )

        try:
            # Call OpenAI API
            response = openai.ChatCompletion.create(
                model="gpt-3.5-turbo",
                max_tokens=150,
                messages=[
                    {"role": "system", "content": "You are an evaluator for an online learning platform."},
                    {"role": "user", "content": prompt}
                ]
            )
            raw_content = response["choices"][0]["message"]["content"]
            result = json.loads(raw_content)  # Parse the response into JSON

            # Append the result
            results.append({
                "questionID": question_id,
                "questionTitle": question_title,
                "correctAnswer": correct_answer,
                "learnerAnswer": learner_answer,
                "marking": result
            })

        except Exception as e:
            # Handle errors gracefully
            results.append({
                "questionID": question_id,
                "questionTitle": question_title,
                "correctAnswer": correct_answer,
                "learnerAnswer": learner_answer,
                "marking": {
                    "score": 0,
                    "feedback": f"Error during processing: {str(e)}"
                }
            })

    return results

def main():
    """
    Main function to handle file input and output.
    """
    if len(sys.argv) < 3:
        print("Usage: python automark_assessment.py <input_json_path> <output_json_path>")
        sys.exit(1)

    input_path = sys.argv[1]
    output_path = sys.argv[2]

    try:
        # Load the input JSON file
        with open(input_path, "r") as f:
            submitted_answers = json.load(f)

        # Debug input data
        print("Loaded data from JSON file:")
        print(json.dumps(submitted_answers, indent=4))

        # Process the questions
        results = mark_questions(submitted_answers)

        # Save the results to the output file
        with open(output_path, "w") as f:
            json.dump(results, f, indent=4)

        print(f"Results saved to {output_path}")

    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()
