<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Python Quiz 1 - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student.css?v=6">
</head>

<body>

<div class="topbar">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="brand">Quiz Master</div>
        <a href="index.php" class="btn btn-outline-primary btn-sm">Back to Dashboard</a>
    </div>
</div>

<div class="container">

    <div class="hero">
        <h1>Python Quiz 1</h1>
        <p class="mb-0">Answer all questions below, then submit to see your grade.</p>
    </div>

    <form id="quizForm" class="mt-4">

        <div class="question-card">
            <h5>1. What function displays output in Python?</h5>
            <label class="answer-option"><input type="radio" name="q1" value="a"> input()</label>
            <label class="answer-option"><input type="radio" name="q1" value="b"> print()</label>
            <label class="answer-option"><input type="radio" name="q1" value="c"> display()</label>
        </div>

        <div class="question-card">
            <h5>2. Which data type stores whole numbers?</h5>
            <label class="answer-option"><input type="radio" name="q2" value="a"> string</label>
            <label class="answer-option"><input type="radio" name="q2" value="b"> float</label>
            <label class="answer-option"><input type="radio" name="q2" value="c"> int</label>
        </div>

        <div class="question-card">
            <h5>3. Which symbol starts a comment in Python?</h5>
            <label class="answer-option"><input type="radio" name="q3" value="a"> //</label>
            <label class="answer-option"><input type="radio" name="q3" value="b"> #</label>
            <label class="answer-option"><input type="radio" name="q3" value="c"> --</label>
        </div>

        <div class="question-card">
            <h5>4. Which value is a string?</h5>
            <label class="answer-option"><input type="radio" name="q4" value="a"> 100</label>
            <label class="answer-option"><input type="radio" name="q4" value="b"> "Hello"</label>
            <label class="answer-option"><input type="radio" name="q4" value="c"> True</label>
        </div>

        <button type="button" class="btn btn-main btn-lg w-100 mb-4" onclick="submitQuiz()">
            Submit Quiz
        </button>

    </form>

    <div id="resultBox" class="result-box mb-5"></div>

</div>

<script>
    function submitQuiz() {
        const answerKey = {
            q1: "b",
            q2: "c",
            q3: "b",
            q4: "b"
        };

        let totalQuestions = Object.keys(answerKey).length;
        let correctAnswers = 0;

        for (let question in answerKey) {
            let selectedAnswer = document.querySelector(`input[name="${question}"]:checked`);

            if (selectedAnswer && selectedAnswer.value === answerKey[question]) {
                correctAnswers++;
            }
        }

        let score = Math.round((correctAnswers / totalQuestions) * 100);

        let attempts = JSON.parse(localStorage.getItem("pythonQuizAttempts")) || [];

        attempts.push({
            quiz: "Python Quiz 1",
            attempt: attempts.length + 1,
            score: score,
            correct: correctAnswers,
            total: totalQuestions,
            status: "Submitted",
            date: new Date().toLocaleDateString()
        });

        localStorage.setItem("pythonQuizAttempts", JSON.stringify(attempts));
        localStorage.setItem("pythonQuizScore", score);
        localStorage.setItem("pythonQuizCorrect", correctAnswers);
        localStorage.setItem("pythonQuizTotal", totalQuestions);

        let resultBox = document.getElementById("resultBox");
        resultBox.style.display = "block";

        resultBox.innerHTML = `
            <h3>Quiz Submitted ✅</h3>
            <p>You got <strong>${correctAnswers}</strong> out of <strong>${totalQuestions}</strong> correct.</p>
            <h4>Your Grade: ${score}%</h4>
            <a href="quiz_results.php" class="btn btn-main mt-3">View Results Page</a>
        `;

        resultBox.scrollIntoView({ behavior: "smooth" });
    }
</script>

</body>
</html>