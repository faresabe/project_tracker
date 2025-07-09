<div style="padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2 style="margin-bottom: 20px;">Create New Project</h2>
        
        <form method="POST" class="project-form ajax-form" data-ajax="true" data-redirect="?page=projects">
            <div class="form-group">
                <label for="projectTitle">Project Title *</label>
                <input type="text" id="projectTitle" name="title" class="form-control" required 
                       placeholder="Enter project title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                <div class="field-error-container"></div>
            </div>
            
            <div class="form-group">
                <label for="projectDescription">Project Description *</label>
                <textarea id="projectDescription" name="description" class="form-control" 
                          rows="4" required placeholder="Describe your project..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                <div class="field-error-container"></div>
            </div>
            
            <div class="form-group">
                <label for="projectStatus">Initial Status</label>
                <select id="projectStatus" name="status" class="form-control">
                    <option value="pending" <?php echo ($_POST['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="ongoing" <?php echo ($_POST['status'] ?? '') === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="completed" <?php echo ($_POST['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="projectPriority">Priority</label>
                <select id="projectPriority" name="priority" class="form-control">
                    <option value="low" <?php echo ($_POST['priority'] ?? '') === 'low' ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($_POST['priority'] ?? 'medium') === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($_POST['priority'] ?? '') === 'high' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="projectDeadline">Deadline (Optional)</label>
                <input type="date" id="projectDeadline" name="deadline" class="form-control" 
                       value="<?php echo htmlspecialchars($_POST['deadline'] ?? ''); ?>">
            </div>
            
            <div class="form-actions">
                <button type="submit" name="create_project" class="btn btn-primary">
                    <span class="btn-text">Create Project</span>
                    <span class="btn-loading" style="display: none;">Creating...</span>
                </button>
                <a href="?page=home" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .project-form {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
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

    .form-control.error {
        border-color: #dc3545;
        box-shadow: 0 0 0 2px rgba(220,53,69,0.25);
    }

    .field-error-container {
        min-height: 20px;
    }

    .field-error {
        color: #dc3545;
        font-size: 12px;
        margin-top: 5px;
        display: block;
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
        position: relative;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        background: #0056b3;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #545b62;
    }

    .btn-loading {
        display: none;
    }

    .btn.loading .btn-text {
        display: none;
    }

    .btn.loading .btn-loading {
        display: inline;
    }

    /* Form validation styles */
    .form-group.has-error .form-control {
        border-color: #dc3545;
    }

    .form-group.has-success .form-control {
        border-color: #28a745;
    }

    @media (max-width: 768px) {
        .project-form {
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
