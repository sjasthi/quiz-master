<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Python Quiz 1 - Quiz Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/quizmaster.css?v=1">
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
        <p class="mb-0">Answer the questions below and click <strong>Submit Quiz</strong> inside the quiz.
            Your score then appears here so you can record the attempt.</p>
    </div>

    <!-- The quiz HTML file is loaded inside an iframe. When the student submits,
         it posts the score up to this page (see assets/js/quiz_parent.js). -->
    <iframe
        id="quizFrame"
        class="quiz-frame mt-4"
        src="../quizzes/python/quiz1.html"
        title="Python Quiz 1">
    </iframe>

    <div id="scoreBox" class="score-box"></div>

    <div class="mt-4 mb-2">
        <button id="submitButton" class="btn btn-main btn-lg w-100" onclick="recordScore()" disabled>
            Record My Score
        </button>
        <p class="hint text-center">Finish the quiz above to enable this button.</p>
    </div>

    <div class="mb-5"></div>

</div>

<script src="../assets/js/quiz_parent.js"></script>

</body>
</html>
