<?php
require_once 'config.php';
requireAuth();
requireRole('facturation');

$user = getCurrentUser();

// Récupérer les factures en attente
$stmt = $pdo->query("
    SELECT i.*, e.name as establishment_name, tr.request_number, tr.patient_name, tr.transport_type
    FROM invoices i
    JOIN establishments e ON i.establishment_id = e.id
    JOIN transport_requests tr ON i.transport_request_id = tr.id
    WHERE i.status = 'en_attente'
    ORDER BY i.created_at ASC
");
$pending_invoices = $stmt->fetchAll();

// Traiter la validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $invoice_id = $_POST['invoice_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'validate') {
            $pdo->prepare("UPDATE invoices SET status = 'validée', validation_date = NOW(), validated_by = ? WHERE id = ?")->execute([$user['id'], $invoice_id]);
            
            // Notification
            $stmt = $pdo->prepare("SELECT transport_request_id FROM invoices WHERE id = ?");
            $stmt->execute([$invoice_id]);
            $inv = $stmt->fetch();
            
            $pdo->prepare("INSERT INTO notifications (transport_request_id, message, type) 
                          VALUES (?, ?, 'facturation')")->execute([$inv['transport_request_id'], "Facture validée"]);
        }

        header('Location: validate_invoices.php?message=Action effectuée');
        exit();
    } catch (PDOException $e) {
        // Error handling
    }
}

$message = $_GET['message'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valider factures - AmbulancePro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 30px;
            color: #667eea;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            color: #bbb;
            text-decoration: none;
            padding: 12px;
            display: block;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #667eea;
            color: white;
        }

        .main {
            flex: 1;
            margin-left: 250px;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
        }

        .btn-logout {
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .invoice-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 15px;
            border-left: 4px solid #fff3cd;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .invoice-number {
            font-size: 16px;
            font-weight: 700;
            color: #333;
        }

        .invoice-establishment {
            color: #667eea;
            font-weight: 500;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail {
            font-size: 14px;
        }

        .detail-label {
            color: #999;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .detail-value {
            color: #333;
            font-weight: 500;
        }

        .amount {
            font-size: 18px;
            color: #667eea;
            font-weight: 700;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-validate {
            background: #27ae60;
            color: white;
        }

        .btn-validate:hover {
            background: #229954;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main {
                margin-left: 0;
            }

            .invoice-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="sidebar">
            <h2>AmbulancePro</h2>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <li><a href="invoices.php">Factures</a></li>
                <li><a href="validate_invoices.php" class="active">Valider factures</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Valider les factures</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <?php if ($message): ?>
                    <div class="alert"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <?php if (empty($pending_invoices)): ?>
                    <div class="empty-state">
                        <p>Aucune facture en attente de validation.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pending_invoices as $inv): ?>
                        <div class="invoice-card">
                            <div class="invoice-header">
                                <div>
                                    <div class="invoice-number"><?php echo htmlspecialchars($inv['invoice_number']); ?></div>
                                    <div class="invoice-establishment"><?php echo htmlspecialchars($inv['establishment_name']); ?></div>
                                </div>
                                <div class="amount"><?php echo number_format($inv['amount'], 2, ',', ' '); ?> €</div>
                            </div>

                            <div class="invoice-details">
                                <div class="detail">
                                    <div class="detail-label">Demande</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($inv['request_number']); ?></div>
                                </div>
                                <div class="detail">
                                    <div class="detail-label">Patient</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($inv['patient_name']); ?></div>
                                </div>
                                <div class="detail">
                                    <div class="detail-label">Type</div>
                                    <div class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $inv['transport_type'])); ?></div>
                                </div>
                            </div>

                            <div class="actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="invoice_id" value="<?php echo $inv['id']; ?>">
                                    <input type="hidden" name="action" value="validate">
                                    <button type="submit" class="btn btn-validate">Valider la facture</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
