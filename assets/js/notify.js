if ("Notification" in window) {
  Notification.requestPermission();
}

document.querySelectorAll(".task").forEach(task => {
  const dueTime = new Date(task.dataset.time.replace(" ", "T")).getTime();
  const taskName = task.dataset.task;

  if (isNaN(dueTime)) return;

  const delay = dueTime - Date.now();

  if (delay > 0) {
    setTimeout(() => {
      if (Notification.permission === "granted") {
        new Notification("⏰ Task Reminder", {
          body: `Task "${taskName}" deadline reached`
        });
      }
    }, delay);
  }
});