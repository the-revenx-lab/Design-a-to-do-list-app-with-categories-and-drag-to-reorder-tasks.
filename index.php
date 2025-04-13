<?php
function runPython($args) {
    $command = escapeshellcmd("python tasks.py $args");
    $output = shell_exec($command);
    return $output;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        echo runPython("add \"{$_POST['title']}\" \"{$_POST['category']}\"");
    } elseif ($action === 'delete') {
        echo runPython("delete {$_POST['id']}");
    } elseif ($action === 'complete') {
        echo runPython("complete {$_POST['id']} {$_POST['status']}");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    echo runPython("get");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>✨ TaskZen - To-Do List</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.4/lottie.min.js"></script>
</head>
<body class="bg-gradient-to-r from-indigo-100 to-yellow-100 min-h-screen flex justify-center items-center p-6">
  <div class="bg-white shadow-2xl rounded-3xl p-8 w-full max-w-3xl animate-fade-in">
    <h2 class="text-3xl font-bold text-center text-slate-800 mb-6">✨ TaskZen - Your Daily To-Do</h2>
    
 <!-- Task Input Section -->
<div class="flex space-x-4 mb-6">
  <input type="text" id="title" placeholder="Enter a new task..." class="flex-1 p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400">
  <select id="category" class="p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <option value="Work">Work</option>
    <option value="Personal">Personal</option>
    <option value="Urgent">Urgent</option>
  </select>
  <button onclick="addTask()" class="bg-indigo-600 text-white px-5 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">Add</button>
</div>

    
    <!-- Task List (Scrollable) -->
    <div id="tasks" class="space-y-4 overflow-y-auto max-h-60 mb-6"></div>
    
    <!-- Task Chart -->
    <div class="mt-8">
      <canvas id="taskChart" class="w-full h-64"></canvas>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    let taskData = [];

    // Toggle visibility of the task input section
    function toggleTaskInput() {
      const taskInputSection = document.getElementById('taskInputSection');
      taskInputSection.classList.toggle('hidden');
    }

    // Load tasks from the server
    function loadTasks() {
      fetch('index.php?action=get')
        .then(res => res.json())
        .then(tasks => {
          taskData = tasks;
          const list = document.getElementById('tasks');
          list.innerHTML = '';
          tasks.forEach(task => {
            const el = document.createElement('div');
            el.className = `flex justify-between items-center p-4 bg-slate-100 rounded-xl shadow-md ${task.completed ? 'opacity-60 line-through' : ''}`;
            el.innerHTML = `
              <span class="text-lg">${task.title} <span class="text-sm text-slate-500">(${task.category})</span></span>
              <div class="flex space-x-2">
                <button onclick="markComplete('${task.id}', ${!task.completed})" class="text-green-600 hover:text-green-800 font-bold">${task.completed ? 'Undo' : '✔'}</button>
                <button onclick="deleteTask('${task.id}')" class="text-red-600 hover:text-red-800 font-bold">🗑</button>
              </div>
            `;
            list.appendChild(el);
          });
          drawChart();
        });
    }

    // Add a new task
    function addTask() {
      const title = document.getElementById('title').value;
      const category = document.getElementById('category').value;
      fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&title=${encodeURIComponent(title)}&category=${category}`
      }).then(() => {
        document.getElementById('title').value = '';
        loadTasks();
      });
    }

    // Delete a task
    function deleteTask(id) {
      fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&id=${id}`
      }).then(() => loadTasks());
    }

    // Mark a task as completed or undone
    function markComplete(id, status) {
      fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=complete&id=${id}&status=${status}`
      }).then(() => loadTasks());
    }

    // Draw the chart to display task count by category
    function drawChart() {
      const ctx = document.getElementById('taskChart').getContext('2d');
      const categories = ['Work', 'Personal', 'Urgent'];
      const counts = categories.map(cat => taskData.filter(t => t.category === cat).length);

      if (window.taskChartInstance) {
        window.taskChartInstance.destroy();
      }

      window.taskChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: categories,
          datasets: [{
            label: 'Tasks by Category',
            data: counts,
            backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
            borderRadius: 8
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    loadTasks();
  </script>
</body>
</html>
