<?php
include '../config.php';
include 'header.php';

$stmt = $pdo->query("SELECT id, name, email, created_at FROM users WHERE user_type != 'admin' ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div>
        <h2>إدارة المستخدمين 👥</h2>
        <p>عرض وحذف الحسابات المسجلة في المتجر</p>
    </div>
</div>

<div class="table-section">
    <?php if (count($users) > 0): ?>
    <table class="modern-table">
        <thead>
            <tr>
                <th>اسم المستخدم</th>
                <th>البريد الإلكتروني</th>
                <th>تاريخ الانضمام</th>
                <th style="text-align: left;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                </td>
                <td>
                    <span class="email-text"><?php echo htmlspecialchars($user['email']); ?></span>
                </td>
                <td>
                    <span class="date-text"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></span>
                </td>
                <td>
                    <div style="display: flex; justify-content: flex-end;">
                        <form action="delete_user.php" method="POST" onsubmit="return confirm('تأكيد الحذف؟');"><input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>"><button type="submit" class="action-btn delete" title="حذف"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="no-users-msg">
            <i class="fa-solid fa-users-slash" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
            <p>لا يوجد مستخدمون مسجلون حالياً (باستثناء الأدمن).</p>
        </div>
    <?php endif; ?>
</div>

<style>
    /* --- تنسيقات صفحة إدارة المستخدمين --- */

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .dashboard-header h2 { color: var(--secondary-color); margin-bottom: 5px; }
    .dashboard-header p { color: #94a3b8; font-size: 14px; }

    .table-section {
        background: white;
        padding: 15px;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.02);
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .modern-table th {
        text-align: right;
        color: #94a3b8;
        font-weight: 500;
        padding: 0 20px;
        font-size: 13px;
    }

    .modern-table tbody tr {
        background: #ffffff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    .modern-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .modern-table td {
        padding: 15px 20px;
        vertical-align: middle;
        border-top: 1px solid #f8fafc;
        border-bottom: 1px solid #f8fafc;
    }

    /* تدوير حواف الأسطر */
    .modern-table td:first-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid #f8fafc; }
    .modern-table td:last-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid #f8fafc; }

    /* تفاصيل النصوص */
    .modern-table td strong { color: var(--secondary-color); font-size: 15px; }
    .email-text { color: #64748b; font-size: 14px; }
    .date-text { color: #94a3b8; font-size: 13px; font-family: 'Poppins', sans-serif; }

    /* زر حذف المستخدم المحسن */
    .action-btn.delete {
        background: #fee2e2 !important;
        color: #ef4444 !important;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        text-decoration: none;
        transition: 0.3s;
    }

    .action-btn.delete:hover {
        background: #ef4444 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    .no-users-msg {
        text-align: center;
        padding: 50px;
        color: #94a3b8;
        background: #f8fafc;
        border-radius: 15px;
        border: 2px dashed #e2e8f0;
    }
</style>
</div></body></html>
