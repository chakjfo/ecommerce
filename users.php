<?php
// Ensure this is an admin-only page
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once "db_connection.php";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        :root {
    --primary-color: #4e73df;
    --secondary-color: #1cc88a;
    --dark-color: #2c3e50;
    --light-color: #f8f9fc;
    --danger-color: #e74a3b;
    --warning-color: #f6c23e;
}
/* Sidebar Styles */
#sidebar {
    min-width: 250px;
    max-width: 250px;
    min-height: 100vh;
    background: linear-gradient(180deg, var(--dark-color) 0%, #1a252f 100%);
    color: #fff;
    transition: all 0.3s;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    z-index: 1000;
}

#sidebar.active {
    margin-left: -250px;
}

#sidebar .sidebar-header {
    padding: 1.5rem 1rem;
    background: rgba(0,0,0,0.1);
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

#sidebar .sidebar-header h3 {
    margin: 0;
    font-weight: 700;
    font-size: 1.5rem;
}

#sidebar ul.components {
    padding: 1rem 0;
}

#sidebar ul li a {
    padding: 0.8rem 1.5rem;
    font-size: 0.9rem;
    display: block;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all 0.2s ease-in-out;
    letter-spacing: 0.5px;
}

#sidebar ul li a:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border-left: 3px solid var(--secondary-color);
}

#sidebar ul li.active > a {
    background: rgba(255,255,255,0.05);
    color: #fff;
    border-left: 3px solid var(--primary-color);
}

#sidebar ul li a i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
}

#sidebar ul ul a {
    padding-left: 3rem !important;
    font-size: 0.85rem !important;
    background: rgba(0,0,0,0.15);
}

@media (max-width: 768px) {
    #sidebar {
        margin-left: -250px;
    }
    
    #sidebar.active {
        margin-left: 0;
    }
    
    .sidebarToggle {
        visibility: visible;
    }
}

        ul ul a {
            font-size: 0.9em !important;
            padding-left: 30px !important;
            background: #4b545c;
        }
        .wrapper {
            display: flex;
            width: 100%;
        }
        #content {
            width: 100%;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .user-table th, .user-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .user-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .user-table tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
        .user-table tr.selected {
            background-color: #e2f0ff;
        }
        .edit-btn {
            padding: 8px 15px;
            margin-bottom: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .edit-btn:hover {
            background-color: #45a049;
        }
        .edit-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 50px%;
            height: 50px;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* Adjusted margin-top to 5% */
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-buttons {
            margin-top: 20px;
            text-align: right;
        }
        .save-btn {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .cancel-btn {
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .delete-btn {
            padding: 8px 15px;
            margin-bottom: 20px;
            background-color: rgb(255, 0, 0);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: rgb(255, 0, 0);
        }
        .delete-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            .user-table thead { display: none; }
            .user-table tbody, .user-table tr, .user-table td { display: block; width: 100%; }
            .user-table tr { margin-bottom: 15px; border: 1px solid #ddd; }
            .user-table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            .user-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                text-align: left;
                font-weight: bold;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>

            <ul class="list-unstyled components">
                <li>
                    <a href="admin.php">Home</a>
                </li>
                <li>
                    <a href="#productSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Products</a>
                    <ul class="collapse list-unstyled" id="productSubmenu">
                        <li>
                            <a href="products.php">View All Products</a>
                        </li>
                        <li>
                            <a href="add_product.php">Add New Product</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#categorySubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Categories</a>
                    <ul class="collapse list-unstyled" id="categorySubmenu">
                        <li>
                            <a href="categories.php">View All Categories</a>
                        </li>
                    </ul>
                </li>
                <li class="active">
                    <a href="#userSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">Users</a>
                    <ul class="collapse show list-unstyled" id="userSubmenu">
                        <li class="active">
                            <a href="users.php">View All Users</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#orderSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Orders</a>
                    <ul class="collapse list-unstyled" id="orderSubmenu">
                        <li>
                            <a href="orders.php">View All Orders</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-dark">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <div>
                        <h4>Welcome, <?php echo $_SESSION['Username'] ?? 'Admin'; ?></h4>
                    </div>
                </div>
            </nav>


            <div class="container-fluid">
                <h1>Users Management</h1>
                
                <button id="editUserBtn" class="edit-btn" disabled>Edit Selected User</button>
                <button id="deleteUserBtn" class="delete-btn" disabled>Delete Selected User</button>
                
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-bordered user-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch all users from the database
                            $sql = "SELECT UserID, Username, Email, PhoneNumber, Role FROM users";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr data-id='" . $row["UserID"] . "'>";
                                    echo "<td>" . $row["UserID"] . "</td>";
                                    echo "<td>" . htmlspecialchars($row["Username"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["Email"]) . "</td>";
                                    echo "<td>" . htmlspecialchars($row["PhoneNumber"] ?? 'Not provided') . "</td>";
                                    echo "<td>" . htmlspecialchars($row["Role"]) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No users found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Edit User Modal -->
                <div id="editUserModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Edit User</h2>
                        <form id="editUserForm" method="post">
                            <input type="hidden" id="userId" name="userId">
                            
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phoneNumber">Phone Number:</label>
                                <input type="text" id="phoneNumber" name="phoneNumber">
                            </div>
                            
                            <div class="form-group">
                                <label for="role">Role:</label>
                                <select id="role" name="role">
                                    <option value="customer">Customer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="newPassword">New Password (leave blank to keep current):</label>
                                <input type="password" id="newPassword" name="newPassword">
                            </div>
                            
                            <div class="form-buttons">
                                <button type="button" class="cancel-btn" id="cancelEdit">Cancel</button>
                                <button type="submit" class="save-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this user?</p>
                                <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#usersTable').DataTable({
                "pageLength": 10,
                "lengthChange": true,
                "searching": true,
                "ordering": true
            });
            
            let selectedUserId = null;
            
            // Handle row selection
            $('#usersTable tbody').on('click', 'tr', function() {
                $('#usersTable tbody tr').removeClass('selected');
                $(this).addClass('selected');
                selectedUserId = $(this).data('id');
                $('#editUserBtn').prop('disabled', false);
                $('#deleteUserBtn').prop('disabled', false);
            });
            
            // Edit button click handler
            $('#editUserBtn').on('click', function() {
                if (selectedUserId) {
                    $.ajax({
                        url: 'get_user.php',
                        type: 'GET',
                        data: { userId: selectedUserId },
                        dataType: 'json',
                        success: function(data) {
                            $('#userId').val(data.UserID);
                            $('#username').val(data.Username);
                            $('#email').val(data.Email);
                            $('#phoneNumber').val(data.PhoneNumber);
                            $('#role').val(data.Role);
                            $('#newPassword').val('');
                            
                            $('#editUserModal').css('display', 'block');
                        },
                        error: function() {
                            alert('Error fetching user data');
                        }
                    });
                }
            });
            
            // Close the modal
            $('.close, #cancelEdit').on('click', function() {
                $('#editUserModal').css('display', 'none');
            });
            
            // Close modal if clicking outside of it
            $(window).on('click', function(event) {
                if ($(event.target).is('#editUserModal')) {
                    $('#editUserModal').css('display', 'none');
                }
            });
            
            // Handle form submission
            $('#editUserForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'update_user.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('User updated successfully!');
                            $('#editUserModal').css('display', 'none');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating user');
                    }
                });
            });
            
// Delete button click handler
$('#deleteUserBtn').on('click', function() {
    if (selectedUserId) {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        
        // Set up the confirm delete button click handler
        $('#confirmDeleteBtn').off('click').on('click', function() {
            $.ajax({
                url: 'delete_user.php',
                method: 'POST',
                data: { userId: selectedUserId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Reload the page to reflect the changes
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete user'));
                    }
                },
                error: function() {
                    alert('Error connecting to server');
                }
            });
        });
        
        // Show the delete confirmation modal
        deleteModal.show();
    }
});
        });
    </script>
</body>
</html>