<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - SSC Quiz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f8fafc;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #1e293b;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: white;
            color: #64748b;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .tab-btn.active {
            background: #4f46e5;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4f46e5;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .questions-list {
            display: grid;
            gap: 16px;
        }

        .question-item {
            background: #f8fafc;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }

        .question-text {
            font-weight: 600;
            color: #1e293b;
            flex: 1;
        }

        .question-meta {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-exam {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-topic {
            background: #e0e7ff;
            color: #4338ca;
        }

        .badge-difficulty {
            background: #dcfce7;
            color: #16a34a;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>SSC Quiz Admin</h1>
            <p class="subtitle">Manage questions and view results</p>
        </header>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('add')">Add Question</button>
            <button class="tab-btn" onclick="showTab('questions')">View Questions</button>
            <button class="tab-btn" onclick="showTab('results')">View Results</button>
        </div>

        <div id="add-question" class="tab-content active">
            <div class="card">
                <h2>Add New Question</h2>
                <div id="message"></div>
                
                <form id="question-form">
                    <div class="row">
                        <div class="form-group">
                            <label>Exam Type</label>
                            <select name="exam_type" required>
                                <option value="CGL">CGL</option>
                                <option value="CHSL">CHSL</option>
                                <option value="MTS">MTS</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Topic</label>
                            <select name="topic" required>
                                <option value="Number System">Number System</option>
                                <option value="Percentage">Percentage</option>
                                <option value="Profit & Loss">Profit & Loss</option>
                                <option value="Ratio & Proportion">Ratio & Proportion</option>
                                <option value="Average">Average</option>
                                <option value="Time & Work">Time & Work</option>
                                <option value="Time & Distance">Time & Distance</option>
                                <option value="Simple Interest">Simple Interest</option>
                                <option value="LCM & HCF">LCM & HCF</option>
                                <option value="Algebra">Algebra</option>
                                <option value="Geometry">Geometry</option>
                                <option value="Trigonometry">Trigonometry</option>
                                <option value="Mensuration">Mensuration</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Question Text</label>
                        <textarea name="question_text" required placeholder="Enter your question here..."></textarea>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>Option A</label>
                            <input type="text" name="option_a" required>
                        </div>
                        <div class="form-group">
                            <label>Option B</label>
                            <input type="text" name="option_b" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>Option C</label>
                            <input type="text" name="option_c" required>
                        </div>
                        <div class="form-group">
                            <label>Option D</label>
                            <input type="text" name="option_d" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label>Correct Answer</label>
                            <select name="correct_answer" required>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Difficulty</label>
                            <select name="difficulty" required>
                                <option value="easy">Easy</option>
                                <option value="medium">Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Explanation (Optional)</label>
                        <textarea name="explanation" placeholder="Explain the answer..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Question</button>
                </form>
            </div>
        </div>

        <div id="view-questions" class="tab-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2>All Questions</h2>
                    <button class="btn btn-primary" onclick="loadQuestions()">Refresh</button>
                </div>
                <div id="questions-list" class="questions-list">
                    <p>Loading questions...</p>
                </div>
            </div>
        </div>

        <div id="view-results" class="tab-content">
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h2>Quiz Results</h2>
                    <button class="btn btn-primary" onclick="loadResults()">Refresh</button>
                </div>
                <div id="results-list">
                    <p>Loading results...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            
            document.getElementById(tab === 'add' ? 'add-question' : 
                                   tab === 'questions' ? 'view-questions' : 'view-results').classList.add('active');
            
            event.target.classList.add('active');
            
            if (tab === 'questions') loadQuestions();
            if (tab === 'results') loadResults();
        }

        // Add Question
        document.getElementById('question-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('admin_api.php?action=add_question', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                const messageDiv = document.getElementById('message');
                if (result.success) {
                    messageDiv.className = 'message success';
                    messageDiv.textContent = 'Question added successfully!';
                    e.target.reset();
                } else {
                    messageDiv.className = 'message error';
                    messageDiv.textContent = 'Error: ' + result.error;
                }
                
                setTimeout(() => messageDiv.textContent = '', 5000);
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Load Questions
        async function loadQuestions() {
            try {
                const response = await fetch('admin_api.php?action=get_questions');
                const data = await response.json();
                
                const container = document.getElementById('questions-list');
                
                if (data.questions && data.questions.length > 0) {
                    container.innerHTML = data.questions.map(q => `
                        <div class="question-item">
                            <div class="question-header">
                                <div class="question-text">${q.question_text}</div>
                            </div>
                            <div class="question-meta">
                                <span class="badge badge-exam">${q.exam_type}</span>
                                <span class="badge badge-topic">${q.topic}</span>
                                <span class="badge badge-difficulty">${q.difficulty}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p>No questions found</p>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Load Results
        async function loadResults() {
            try {
                const response = await fetch('admin_api.php?action=get_all_results');
                const data = await response.json();
                
                const container = document.getElementById('results-list');
                
                if (data.results && data.results.length > 0) {
                    container.innerHTML = `
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f1f5f9; text-align: left;">
                                    <th style="padding: 12px;">Name</th>
                                    <th style="padding: 12px;">Exam</th>
                                    <th style="padding: 12px;">Topic</th>
                                    <th style="padding: 12px;">Score</th>
                                    <th style="padding: 12px;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.results.map(r => `
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px;">${r.user_name || 'Anonymous'}</td>
                                        <td style="padding: 12px;">${r.exam_type}</td>
                                        <td style="padding: 12px;">${r.topic}</td>
                                        <td style="padding: 12px; font-weight: bold;">${r.score}%</td>
                                        <td style="padding: 12px;">${new Date(r.created_at).toLocaleDateString()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    container.innerHTML = '<p>No results found</p>';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>
