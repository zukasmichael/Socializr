#!/bin/bash
# Start/Stop/Restart/Info for the php queue in the background
################

# Define vars
commands=('start' 'stop' 'restart' 'info');

# Define functions
getQueuePid () {
    local pidcmd=`ps aux | grep \[c\]li/queue | awk '{print $2}'`
    pid=$pidcmd
}

# Get the first parameter as a command
command=$1


# For info, return at once
if [[ $command = "info" ]]; then
    echo "USER    PID %CPU %MEM  VSZ   RSS   TTY STAT START TIME COMMAND"
    echo $(ps aux | grep \[c\]li/queue)
    exit 1
fi


# Check valid command
match=0
for val in "${commands[@]}"; do
    if [ "${val}" = "$command" ]; then
        match=1
        break
    fi
done
if [[ $match = 0 ]]; then
    echo "This script needs one argument, containing on of the values: [start, stop, restart, info]"
    exit 0
fi


# Get the pid for the queue
getQueuePid


# Process command
if [[ $pid = "" ]]; then
    if [[ $command = "stop" ]]; then
        echo "Queue is not running, nothing to do..."
        exit 1
    fi

    if [[ $command = "restart" ]]; then
        echo "Queue is not running, now starting queue..."
    fi

    # Starting queue:

    nohup php ./cli/queue.php &
    echo "Queue started"
else
    if [[ $command = "start" ]]; then
        echo "Queue is already running, use restart if you want to stop and start the queue."
        exit 1
    fi

    # Stopping queue:
    kill $pid
    echo "Queue stopped"

    if [[ $command = "restart" ]]; then
        # Starting queue:
        nohup php ./cli/queue.php &
        echo "Queue started"
    fi
fi