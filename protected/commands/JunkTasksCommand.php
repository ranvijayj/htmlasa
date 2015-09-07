<?php

class JunkTasksCommand extends CConsoleCommand
{
    public function run($args) {

        echo "Begin of the command\n";

            echo "Starting Junk Files Removing\n";
                CronController::CommandIndex('25sa9k8vtfo4jchg30fc8sjl01','junkfilesremoving');
                    echo "Finishing Junk Files Removing\n";

        echo "End of the command\n";


    }

}
?>
