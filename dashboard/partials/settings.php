<?php
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $theme = $_POST['theme'] ?? 'light';
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    
    if (empty($name) || empty($email)) {
        $error = 'Name and email are required';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email is already taken by another user
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email, $user['id']]);
        
        if ($stmt->fetch()) {
            $error = 'Email address is already taken';
        } else {
            // If password change is requested
            if (!empty($currentPassword) || !empty($newPassword)) {
                if (empty($currentPassword) || empty($newPassword)) {
                    $error = 'Both current and new password are required to change password';
                } elseif (strlen($newPassword) < 6) {
                    $error = 'New password must be at least 6 characters long';
                } else {
                    // Verify current password
                    $query = "SELECT password FROM users WHERE id = ?";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$user['id']]);
                    $userData = $stmt->fetch();
                    
                    if (!verifyPassword($currentPassword, $userData['password'])) {
                        $error = 'Current password is incorrect';
                    } else {
                        // Update with new password
                        $hashedPassword = hashPassword($newPassword);
                        $query = "UPDATE users SET name = ?, email = ?, password = ?, theme = ?, email_notifications = ? WHERE id = ?";
                        $stmt = $db->prepare($query);
                        
                        if ($stmt->execute([$name, $email, $hashedPassword, $theme, $emailNotifications, $user['id']])) {
                            $success = 'Settings updated successfully!';
                            // Update session
                            $_SESSION['user_name'] = $name;
                            $_SESSION['user_email'] = $email;
                            // Refresh user data
                            $user = getCurrentUser();
                        } else {
                            $error = 'Failed to update settings';
                        }
                    }
                }
            } else {
                // Update without password change
                $query = "UPDATE users SET name = ?, email = ?, theme = ?, email_notifications = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if ($stmt->execute([$name, $email, $theme, $emailNotifications, $user['id']])) {
                    $success = 'Settings updated successfully!';
                    // Update session
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    // Refresh user data
                    $user = getCurrentUser();
                } else {
                    $error = 'Failed to update settings';
                }
            }
        }
    }
}
?>

<div style="padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2 style="margin-bottom: 20px;">Account Settings</h2>
        
        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="settings-form">
            <div class="settings-section">
                <h3>Profile Information</h3>
                
                <div class="form-group">
                    <label for="userName">Full Name *</label>
                    <input type="text" id="userName" name="name" class="form-control" required 
                           value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="userEmail">Email Address *</label>
                    <input type="email" id="userEmail" name="email" class="form-control" required 
                           value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Change Password</h3>
                <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    Leave password fields empty if you don't want to change your password.
                </p>
                
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="current_password" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="new_password" class="form-control" minlength="6">
                    <small style="color: #666;">Minimum 6 characters</small>
                </div>
            </div>
            
            <div class="settings-section">
                <h3>Preferences</h3>
                
                <div class="form-group">
                    <label for="theme">Theme</label>
                    <select id="theme" name="theme" class="form-control">
                        <option value="light" <?php echo $user['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $user['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="email_notifications" <?php echo $user['email_notifications'] ? 'checked' : ''; ?>>
                        Receive email notifications
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="update_settings" class="btn btn-primary">Save Changes</button>
                <a href="?page=home" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .settings-form {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        margin-bottom: 30px;
    }

    .settings-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .settings-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .settings-section h3 {
        margin-bottom: 15px;
        color: #2c3e50;
        font-size: 18px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #2c3e50;
    }

    .form-group label input[type="checkbox"] {
        margin-right: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.3s;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #545b62;
    }

    @media (max-width: 768px) {
        .settings-form {
            padding: 20px;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>