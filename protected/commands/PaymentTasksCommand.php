<?php

class PaymentTasksCommand extends CConsoleCommand
{
    public function run($args) {

        echo "Begin of the command\n";

            echo "Starting expirationdatenotification\n";
                CronController::CommandIndex('25sa9k8vtfo4jchg30fc8sjl01','expirationdatenotification');
                    echo "Finishing expirationdatenotification\n";

        echo "End of the command\n";


    }

}
?>
