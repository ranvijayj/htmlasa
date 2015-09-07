<?php

class MailingTasksCommand extends CConsoleCommand
{
    public function run($args) {

        echo "Begin of the command\n\n";

            echo "Starting sheduled testnotification\n";
                CronController::CommandIndex('25sa9k8vtfo4jchg30fc8sjl01','testnotification');
                    echo "Test Notification Finished \n";

            echo "Starting sheduled approval mailling\n";
                            CronController::CommandIndex('25sa9k8vtfo4jchg30fc8sjl01','dailymailing');
                                echo "Approval Mailing Finished \n";

        echo "\nEnd of the command\n";


    }

}
?>
