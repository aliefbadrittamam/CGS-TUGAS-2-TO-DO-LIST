<?php
session_start();

// Inisialisasi array tasks jika belum ada
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Fungsi untuk menambahkan task baru
function addTask($title, $description, $priority) {
    $task = [
        'id' => uniqid(),
        'title' => $title,
        'description' => $description,
        'priority' => $priority,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    $_SESSION['tasks'][] = $task;
}

// Fungsi untuk mendapatkan semua tasks
function getAllTasks() {
    return $_SESSION['tasks'];
}

// Fungsi untuk mendapatkan task berdasarkan ID
function getTaskById($id) {
    foreach ($_SESSION['tasks'] as $task) {
        if ($task['id'] === $id) {
            return $task;
        }
    }
    return null;
}

// Fungsi untuk mengupdate task
function updateTask($id, $title, $description, $priority) {
    foreach ($_SESSION['tasks'] as &$task) {
        if ($task['id'] === $id) {
            $task['title'] = $title;
            $task['description'] = $description;
            $task['priority'] = $priority;
            $task['updated_at'] = date('Y-m-d H:i:s');
            return true;
        }
    }
    return false;
}

// Fungsi untuk menghapus task
function deleteTask($id) {
    foreach ($_SESSION['tasks'] as $key => $task) {
        if ($task['id'] === $id) {
            unset($_SESSION['tasks'][$key]);
            return true;
        }
    }
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addTask($_POST['title'], $_POST['description'], $_POST['priority']);
                break;
            case 'update':
                updateTask($_POST['id'], $_POST['title'], $_POST['description'], $_POST['priority']);
                break;
            case 'delete':
                deleteTask($_POST['id']);
                break;
        }
    }
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle get requests for edit
$editTask = null;
if (isset($_GET['edit'])) {
    $editTask = getTaskById($_GET['edit']);
}

// HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Manajemen Task</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .priority-high { background-color: #fff3cd; }
        .priority-medium { background-color: #d1e7dd; }
        .priority-low { background-color: #cfe2ff; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Aplikasi Manajemen Task</h1>

        <!-- Form untuk menambah/edit task -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><?php echo $editTask ? 'Edit Task' : 'Tambah Task Baru'; ?></h5>
                <form method="post" action="">
                    <input type="hidden" name="action" value="<?php echo $editTask ? 'update' : 'add'; ?>">
                    <?php if ($editTask): ?>
                        <input type="hidden" name="id" value="<?php echo $editTask['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Task</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo $editTask ? htmlspecialchars($editTask['title']) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Task</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $editTask ? htmlspecialchars($editTask['description']) : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="priority" class="form-label">Prioritas</label>
                        <select class="form-select" id="priority" name="priority" required>
                            <option value="">Pilih Prioritas</option>
                            <option value="high" <?php echo ($editTask && $editTask['priority'] == 'high') ? 'selected' : ''; ?>>Tinggi</option>
                            <option value="medium" <?php echo ($editTask && $editTask['priority'] == 'medium') ? 'selected' : ''; ?>>Sedang</option>
                            <option value="low" <?php echo ($editTask && $editTask['priority'] == 'low') ? 'selected' : ''; ?>>Rendah</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $editTask ? 'Update Task' : 'Tambah Task'; ?></button>
                </form>
            </div>
        </div>

        <!-- Tabel untuk menampilkan tasks -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Daftar Task</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Prioritas</th>
                                <th>Dibuat</th>
                                <th>Diperbarui</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (getAllTasks() as $task): ?>
                                <tr class="priority-<?php echo $task['priority']; ?>">
                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                                    <td><?php echo ucfirst($task['priority']); ?></td>
                                    <td><?php echo $task['created_at']; ?></td>
                                    <td><?php echo $task['updated_at']; ?></td>
                                    <td>
                                        <a href="?edit=<?php echo $task['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus task ini?');">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>