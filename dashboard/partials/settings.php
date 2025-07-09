<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    $errors = [];
    
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $errors[] = 'All fields except password are required';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Email is already taken by another user';
    }
    
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update with new password
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password_hash = ? WHERE id = ?");
                $success = $stmt->execute([$first_name, $last_name, $email, $password_hash, $user_id]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                $success = $stmt->execute([$first_name, $last_name, $email, $user_id]);
            }
            
            if ($success) {
                $success_message = 'Settings updated successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $errors[] = 'Failed to update settings';
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    // Get current user data
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<section class="form-section">
    <h2>Settings</h2>
    
    <?php if (!empty($errors)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($success_message)): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" 
                   value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" 
                   value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password (leave blank to keep current)</label>
            <input type="password" id="new_password" name="new_password" 
                   placeholder="Enter new password">
        </div>
        
        <button type="submit" class="create-project">Save Changes</button>
    </form>
    
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
        <h3>Account Statistics</h3>
        <?php
        // Get user statistics
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_projects FROM projects WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_projects = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_tasks FROM tasks t JOIN projects p ON t.project_id = p.id WHERE p.user_id = ?");
        $stmt->execute([$user_id]);
        $total_tasks = $stmt->fetchColumn();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as completed_projects FROM projects WHERE user_id = ? AND status = 'completed'");
        $stmt->execute([$user_id]);
        $completed_projects = $stmt->fetchColumn();
        ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div style="background: #f8f9fb; padding: 20px; border-radius: 12px; text-align: center;">
                <h4 style="margin: 0; color: #1976d2; font-size: 2rem;"><?= $total_projects ?></h4>
                <p style="margin: 5px 0 0 0; color: #666;">Total Projects</p>
            </div>
            
            <div style="background: #f8f9fb; padding: 20px; border-radius: 12px; text-align: center;">
                <h4 style="margin: 0; color: #3bb77e; font-size: 2rem;"><?= $completed_projects ?></h4>
                <p style="margin: 5px 0 0 0; color: #666;">Completed Projects</p>
            </div>
            
            <div style="background: #f8f9fb; padding: 20px; border-radius: 12px; text-align: center;">
                <h4 style="margin: 0; color: #f7b731; font-size: 2rem;"><?= $total_tasks ?></h4>
                <p style="margin: 5px 0 0 0; color: #666;">Total Tasks</p>
            </div>
        </div>
    </div>
</section>