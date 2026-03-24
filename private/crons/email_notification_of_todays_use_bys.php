<?php
    require_once('/var/www/artifact-management-tool/private/initialize.php');

    date_default_timezone_set('America/New_York');
    $current_hour = (int) date('G');

    file_put_contents(
        __FILE__ . '.log',
        __FILE__ . " began running at " . date('Y-m-d G:i:s') . " (hour: $current_hour)\n",
        FILE_APPEND
    );

    $stmt = mysqli_prepare($db, "SELECT id FROM users WHERE daily_email = 1 AND daily_email_hour = ?");
    mysqli_stmt_bind_param($stmt, "i", $current_hour);
    mysqli_stmt_execute($stmt);
    $users = mysqli_stmt_get_result($stmt);

    foreach ($users as $user) {

        $user_id = $user['id'];

        echo "user id $user_id \n";

        $count_to_notify_about = email_artifact_use_notice($user_id);

        echo "count to notify about $count_to_notify_about \n";

    }

    file_put_contents(
        __FILE__ . '.log', 
        __FILE__ . ' finished running at ' . date('Y-m-d G:i:s') . "\n",
        FILE_APPEND
    );

?>