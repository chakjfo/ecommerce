<?php
// Ensure this is an admin-only page
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once "db_connection.php";

// Fetch orders
// Fetch orders grouped by order_id
// Fetch orders
$order_query = "SELECT 
                o.OrderID,
                u.username AS customer_name,
                o.OrderDate,
                o.TotalAmount,
                o.payment_method
               FROM orders o
               JOIN users u ON o.UserID = u.UserID
               ORDER BY o.OrderDate DESC";
$order_result = $conn->query($order_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Keep existing styles, add order-specific styles */
        .order-details-modal .modal-dialog {
            max-width: 700px;
        }
        .order-items-list {
            list-style: none;
            padding: 0;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .waybill-header {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }
        #sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #343a40;
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar.active {
            margin-left: -250px;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #212529;
        }
        #sidebar ul.components {
            padding: 20px 0;
            border-bottom: 1px solid #4b545c;
        }
        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }
        #sidebar ul li a {
            padding: 10px;
            font-size: 1.1em;
            display: block;
            color: #fff;
            text-decoration: none;
        }
        #sidebar ul li a:hover {
            color: #000;
            background: #fff;
        }
        #sidebar ul li.active > a,
        a[aria-expanded="true"] {
            color: #fff;
            background: #6d7fcc;
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
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #sidebarCollapse span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Updated Sidebar -->
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
                    </ul>
                </li>
                <li>
                    <a href="#orderSubmenu" data-bs-toggle="collapse" aria-expanded="true" class="dropdown-toggle">Orders</a>
                    <ul class="collapse show list-unstyled" id="orderSubmenu">
                        <li class="active">
                            <a href="orders.php">View All Orders</a>
                        </li>
                        <li>
                            <a href="order_details.php">Order Analytics</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#userSubmenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Users</a>
                    <ul class="collapse list-unstyled" id="userSubmenu">
                        <li>
                            <a href="users.php">View All Users</a>
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
            <button type="button" id="sidebarCollapse" class="btn btn-dark">
                <i class="fas fa-align-left"></i>
                <span>Toggle Sidebar</span>
            </button>
            <div class="container mt-5">
                <h2>Order Management</h2>
                <table class="table table-bordered" id="orderTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php while ($order = $order_result->fetch_assoc()): ?>
        <tr>
            <td><?= $order['OrderID'] ?></td>
            <td><?= htmlspecialchars($order['customer_name']) ?></td>
            <td><?= date('M d, Y', strtotime($order['OrderDate'])) ?></td>
            <td>$<?= number_format($order['TotalAmount'], 2) ?></td>
            <td>
                <button class="btn btn-info btn-sm view-order" 
                        data-id="<?= $order['OrderID'] ?>"
                        data-bs-toggle="modal" 
                        data-bs-target="#orderDetailsModal">
                    <i class="fas fa-eye"></i> View
                </button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade order-details-modal" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header waybill-header">
                    <h5 class="modal-title">Order #<span id="orderId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
    <div class="row mb-3">
        <div class="col-6">
            <strong>Order Date:</strong> <span id="orderDate"></span>
        </div>
        <div class="col-6">
            <strong>Customer:</strong> <span id="customerName"></span>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-6">
            <strong>Delivery Status:</strong> <span id="deliveryStatus"></span>
        </div>
        <div class="col-6">
            <strong>Estimated Delivery:</strong> <span id="deliveryDate"></span>
        </div>
    </div>
    <div class="mb-3">
        <strong>Shipping Address:</strong> 
        <div id="shippingAddress"></div>
    </div>
    <div class="mb-3">
        <strong>Payment Method:</strong> 
        <span id="paymentMethod"></span>
    </div>
    <h6>Order Items:</h6>
    <ul class="order-items-list" id="orderItems"></ul>
    <div class="text-end">
        <h5>Total: $<span id="orderTotal"></span></h5>
    </div>
</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
                document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.getElementById('sidebarCollapse').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
        });

        $(document).ready(function() {
            $('#orderTable').DataTable({
                "order": [[0, "desc"]],
                "columns": [
                    { "width": "10%" },
                    null,
                    { "width": "15%" },
                    { "width": "15%" },
                    { "width": "10%" }
                ]
            });

            $('.view-order').click(function() {
    const orderId = $(this).data('id');
    $.ajax({
        url: 'get_order_details.php',
        method: 'POST',
        data: { order_id: orderId },
        dataType: 'json',
        success: function(response) {
            // Update modal content
            $('#orderId').text(response.order.OrderID);
            $('#customerName').text(response.order.customer_name);
            $('#orderDate').text(new Date(response.order.OrderDate).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }));
            
            // Shipping and payment info
            $('#shippingAddress').text(response.order.shipping_address);
            $('#paymentMethod').text(response.order.payment_method);
            $('#deliveryStatus').text(response.order.delivery_status);
            $('#deliveryDate').text(response.order.delivery_date);
            
            // Order items
            $('#orderItems').empty();
            response.items.forEach(item => {
                $('#orderItems').append(
                    `<li class="order-item">
                        <div>${item.ProductName} (ID: ${item.product_id})</div>
                        <div>
                            Qty: ${item.quantity} × 
                            $${item.price.toFixed(2)}
                        </div>
                    </li>`
                );
            });
            
            // Total amount
            $('#orderTotal').text(response.order.TotalAmount.toFixed(2));
        }
    });

            });
        });
    </script>
</body>
</html>