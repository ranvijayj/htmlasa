<?php

class CacheTasksCommand extends CConsoleCommand
{
    public function run($args) {

        echo "Begin of the command\n";

            echo "Starting Cashe Files Removing\n";
                CronController::CommandIndex('25sa9k8vtfo4jchg30fc8sjl01','cachefilesremoving');
                    echo "Finishing Cache Files Removing\n";

        echo "End of the command\n";


    }

}
?>
