<?php
/**
 * SEND_WHATSAPP_REMINDER.PHP
 * Cron job untuk mengirim reminder WhatsApp H-5 dan H-1
 * Setup: curl https://yoursite.com/api/send_whatsapp_reminder.php
 */

require_once __DIR__ . '/config.php';

// Twilio Configuration
// Dapatkan dari: https://www.twilio.com/console
define('TWILIO_ACCOUNT_SID', 'ACb85bb6ff9bf1d6bf09f5bad0697ce812');
define('TWILIO_AUTH_TOKEN', '172d9de90d7f6297b435a0a51629a2bb');
define('TWILIO_WHATSAPP_NUMBER', 'whatsapp:+14155552671'); // Nomor WhatsApp Twilio (jangan ubah)

/**
 * Function untuk kirim WhatsApp via Twilio
 */
function sendWhatsAppMessage($toNumber, $message) {
    $accountSid = TWILIO_ACCOUNT_SID;
    $authToken = TWILIO_AUTH_TOKEN;
    
    // Format nomor ke WhatsApp format: whatsapp:+62812345678
    if (!strpos($toNumber, 'whatsapp:')) {
        $toNumber = 'whatsapp:' . $toNumber;
    }
    
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";
    
    $data = [
        'From' => TWILIO_WHATSAPP_NUMBER,
        'To' => $toNumber,
        'Body' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 201) {
        return true;
    } else {
        error_log("WhatsApp Send Error: " . $result);
        return false;
    }
}

/**
 * Function untuk cek dan kirim reminder
 */
function checkAndSendReminders() {
    global $conn;
    
    $today = new DateTime();
    $todayStr = $today->format('Y-m-d');
    
    // Hitung tanggal H-5 dan H-1
    $h_minus_5 = (clone $today)->modify('-5 days')->format('Y-m-d');
    $h_minus_1 = (clone $today)->modify('-1 days')->format('Y-m-d');
    
    // Query: Cari jadwal yang jatuh pada H-5 atau H-1 yang belum dikirim reminder
    $query = "
        SELECT 
            ims.id,
            ims.scheduled_date,
            ims.child_id,
            immu.name as vaccine_name,
            c.name as child_name,
            u.whatsapp_number,
            u.username
        FROM immunization_schedules ims
        LEFT JOIN immunization_types immu ON ims.immunization_type_id = immu.id
        LEFT JOIN children c ON ims.child_id = c.id
        LEFT JOIN users u ON c.user_id = u.id
        WHERE ims.status = 'pending'
        AND (ims.scheduled_date = DATE_ADD(?, INTERVAL 5 DAY)
             OR ims.scheduled_date = DATE_ADD(?, INTERVAL 1 DAY))
        AND (ims.reminder_sent_h5 = 0 OR ims.reminder_sent_h1 = 0)
        AND u.whatsapp_number IS NOT NULL
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $todayStr, $todayStr);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sentCount = 0;
    
    while ($row = $result->fetch_assoc()) {
        $scheduledDate = new DateTime($row['scheduled_date']);
        $daysUntil = $today->diff($scheduledDate)->days;
        $reminderType = '';
        
        // Tentukan tipe reminder berdasarkan selisih hari
        if ($daysUntil == 5) {
            $reminderType = 'h5';
        } elseif ($daysUntil == 1) {
            $reminderType = 'h1';
        }
        
        if (empty($reminderType)) {
            continue;
        }
        
        // Buat pesan WhatsApp
        $message = createReminderMessage(
            $row['child_name'],
            $row['vaccine_name'],
            $row['scheduled_date'],
            $daysUntil,
            $row['username']
        );
        
        // Kirim WhatsApp
        $whatsappNumber = $row['whatsapp_number'];
        
        if (sendWhatsAppMessage($whatsappNumber, $message)) {
            // Update database: tandai bahwa reminder sudah dikirim
            $updateColumn = ($reminderType == 'h5') ? 'reminder_sent_h5' : 'reminder_sent_h1';
            $updateQuery = "UPDATE immunization_schedules SET {$updateColumn} = 1 WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $row['id']);
            $updateStmt->execute();
            $updateStmt->close();
            
            $sentCount++;
            error_log("✅ WhatsApp reminder sent to {$whatsappNumber} for {$row['child_name']} - {$row['vaccine_name']}");
        } else {
            error_log("❌ Failed to send WhatsApp reminder to {$whatsappNumber}");
        }
    }
    
    $stmt->close();
    return $sentCount;
}

/**
 * Function untuk membuat pesan reminder
 */
function createReminderMessage($childName, $vaccineName, $scheduledDate, $daysUntil, $parentName) {
    $date = new DateTime($scheduledDate);
    $dateFormat = $date->format('d/m/Y');
    $timeMsg = '';
    
    if ($daysUntil == 5) {
        $timeMsg = "Dalam 5 hari";
    } elseif ($daysUntil == 1) {
        $timeMsg = "Besok";
    }
    
    $message = "🔔 *REMINDER JADWAL IMUNISASI*\n\n";
    $message .= "Halo {$parentName},\n\n";
    $message .= "Anak Anda *{$childName}* memiliki jadwal vaksin:\n\n";
    $message .= "💉 Vaksin: *{$vaccineName}*\n";
    $message .= "📅 Tanggal: *{$dateFormat}*\n";
    $message .= "⏰ {$timeMsg}\n\n";
    $message .= "Pastikan membawa anak ke fasilitas kesehatan tepat waktu.\n";
    $message .= "Jangan lupa membawa kartu imunisasi.\n\n";
    $message .= "Terima kasih,\n";
    $message .= "Sistem IMUNO";
    
    return $message;
}

// ============== MAIN EXECUTION ==============

try {
    // Cek konfigurasi Twilio
    if (TWILIO_ACCOUNT_SID === 'YOUR_TWILIO_ACCOUNT_SID') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Twilio configuration not set. Please update config.'
        ]);
        exit();
    }
    
    // Jalankan reminder check
    $sent = checkAndSendReminders();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Successfully sent {$sent} WhatsApp reminders",
        'sent_count' => $sent,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    error_log("Reminder cron job executed. Sent: {$sent} messages");
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    error_log("Reminder cron job error: " . $e->getMessage());
}

$conn->close();
?>