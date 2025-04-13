# tasks.py
import json
import sys

TASKS_FILE = 'tasks_data/tasks.json'

def load_tasks():
    try:
        with open(TASKS_FILE, 'r') as file:
            return json.load(file)
    except:
        return []

def save_tasks(tasks):
    with open(TASKS_FILE, 'w') as file:
        json.dump(tasks, file, indent=4)

def add_task(title, category):
    tasks = load_tasks()
    new_task = {
        'id': str(len(tasks) + 1),
        'title': title,
        'category': category,
        'completed': False
    }
    tasks.append(new_task)
    save_tasks(tasks)
    print(json.dumps(new_task))

def get_all_tasks():
    tasks = load_tasks()
    print(json.dumps(tasks))

def delete_task(task_id):
    tasks = load_tasks()
    tasks = [task for task in tasks if task['id'] != task_id]
    save_tasks(tasks)
    print(json.dumps({'status': 'deleted'}))

def complete_task(task_id, status):
    tasks = load_tasks()
    for task in tasks:
        if task['id'] == task_id:
            task['completed'] = status
    save_tasks(tasks)
    print(json.dumps({'status': 'updated'}))

# CLI interface for PHP to call
if __name__ == '__main__':
    action = sys.argv[1]
    if action == 'get':
        get_all_tasks()
    elif action == 'add':
        add_task(sys.argv[2], sys.argv[3])
    elif action == 'delete':
        delete_task(sys.argv[2])
    elif action == 'complete':
        complete_task(sys.argv[2], sys.argv[3].lower() == 'true')
