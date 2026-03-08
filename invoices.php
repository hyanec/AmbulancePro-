<?php
require_once 'config.php';
requireAuth();
requireRole('facturation');

// Récupérer les factures
$stmt = $pdo->query("
    SELECT i.*, e.name as establishment_name, tr.request_number, tr.patient_name
    FROM invoices i
    JOIN establishments e ON i.establishment_id = e.id
    JOIN transport_requests tr ON i.transport_request_id = tr.id
    ORDER BY i.created_at DESC
");
$invoices = $stmt->fetchAll();

// Calculer les statistiques
$total_amount = 0;
$paid_amount = 0;
foreach ($invoices as $inv) {
    $total_amount += $inv['amount'];
    if ($inv['status'] === 'payée') {
        $paid_amount += $inv['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factures - AmbulancePro</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .stat-card .number {
            font-size: 28px;
            color: #667eea;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        thead {
            background: #f9f9f9;
            border-bottom: 2px solid #eee;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-validée {
            background: #cce5ff;
            color: #004085;
        }

        .status-payée {
            background: #d4edda;
            color: #155724;
        }

        .amount {
            font-weight: 600;
            color: #333;
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

            table {
                font-size: 12px;
            }

            th, td {
                padding: 10px;
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
                <li><a href="invoices.php" class="active">Factures</a></li>
                <li><a href="validate_invoices.php">Valider factures</a></li>
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <div class="main">
            <div class="header">
                <h1>Gestion des factures</h1>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
            </div>

            <div class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Montant total</h3>
                        <div class="number"><?php echo number_format($total_amount, 2, ',', ' '); ?> €</div>
                    </div>
                    <div class="stat-card">
                        <h3>Montant payé</h3>
                        <div class="number"><?php echo number_format($paid_amount, 2, ',', ' '); ?> €</div>
                    </div>
                    <div class="stat-card">
                        <h3>Montant en attente</h3>
                        <div class="number"><?php echo number_format($total_amount - $paid_amount, 2, ',', ' '); ?> €</div>
                    </div>
                </div>

                <?php if (empty($invoices)): ?>
                    <div class="empty-state">
                        <p>Aucune facture.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Numéro facture</th>
                                <th>Établissement</th>
                                <th>Demande</th>
                                <th>Patient</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['establishment_name']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['request_number']); ?></td>
                                    <td><?php echo htmlspecialchars($inv['patient_name']); ?></td>
                                    <td class="amount"><?php echo number_format($inv['amount'], 2, ',', ' '); ?> €</td>
                                    <td>
                                        <span class="status-badge status-<?php echo str_replace('_', '', $inv['status']); ?>">
                                            <?php echo ucfirst($inv['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($inv['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
