<?php 
    file_put_contents(
        __FILE__ . '.log', 
        __FILE__ . ' began running at ' . date('Y-m-d G:i:s') . "\n",
        FILE_APPEND
    );
    
    require_once('/var/www/artifact-management-tool/private/initialize.php');

    $users = query("SELECT id FROM users");

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