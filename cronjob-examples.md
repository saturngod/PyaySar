# Cron Job Examples for Invoice Management System

This file contains examples of cron jobs that can be set up for maintaining and monitoring the Invoice Management System in production.

## Daily Tasks

### 1. Daily Cleanup (2:00 AM)
```bash
0 2 * * * cd /path/to/your/project && php artisan cleanup:old-files --days=30
```

### 2. Daily Database Backup (3:00 AM)
```bash
0 3 * * * cd /path/to/your/project && php artisan backup:run
```

### 3. Generate Overdue Invoice Reports (9:00 AM)
```bash
0 9 * * * cd /path/to/your/project && php artisan invoices:check-overdue --send-email
```

### 4. Cache Optimization (Every 6 hours)
```bash
0 */6 * * * cd /path/to/your/project && php artisan cache:prune-stale-tags
```

## Weekly Tasks

### 1. Weekly Health Check (Sunday 4:00 AM)
```bash
0 4 * * 0 cd /path/to/your/project && php artisan system:health-check
```

### 2. Log Rotation (Sunday 1:00 AM)
```bash
0 1 * * 0 cd /path/to/your/project && php artisan logs:rotate
```

### 3. Security Audit (Saturday 2:00 AM)
```bash
0 2 * * 6 cd /path/to/your/project && php artisan security:audit
```

## Monthly Tasks

### 1. Monthly Performance Report (1st day of month, 5:00 AM)
```bash
0 5 1 * * cd /path/to/your/project && php artisan reports:monthly --send-admin
```

### 2. Archive Old Records (15th of month, 2:00 AM)
```bash
0 2 15 * * cd /path/to/your/project && php artisan archive:old-records --months=12
```

## Queue Workers

### 1. Ensure Queue Workers Are Running (Every 5 minutes)
```bash
*/5 * * * * cd /path/to/your/project && php artisan queue:monitor
```

### 2. Restart Queue Workers Daily
```bash
0 0 * * * cd /path/to/your/project && php artisan queue:restart
```

## System Monitoring

### 1. Check Disk Space (Every hour)
```bash
0 * * * * df -h /path/to/your/project | tail -n1 | awk '{print $5}' | sed 's/%//' | if [ $(cat) -gt 80 ]; then echo "Disk usage high"; fi
```

### 2. Monitor Memory Usage (Every 30 minutes)
```bash
*/30 * * * * free -m | grep Mem | awk '{print $3/$2 * 100.0}' | if [ $(cat) -gt 85 ]; then echo "Memory usage high"; fi
```

## SSL Certificate Renewal

### 1. Check SSL Certificate Expiry (Daily at 8:00 AM)
```bash
0 8 * * * cd /path/to/your/project && php artisan ssl:check --warn-days=30
```

### 2. Auto-renew Let's Encrypt Certificates (Daily at 7:00 AM)
```bash
0 7 * * * certbot renew --quiet
```

## Setting Up Cron Jobs

### On Ubuntu/Debian:
```bash
# Edit crontab
crontab -e

# Add these lines (adjust paths as needed):
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

### Using Laravel Scheduler:
Add this to your crontab (recommended approach):
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Then define the tasks in `app/Console/Kernel.php`:

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Daily cleanup at 2 AM
        $schedule->command('cleanup:old-files --days=30')
                 ->dailyAt('02:00')
                 ->appendOutputTo(storage_path('logs/cleanup.log'));

        // Daily backup at 3 AM
        $schedule->command('backup:run')
                 ->dailyAt('03:00')
                 ->onSuccess(function () {
                     // Send notification on successful backup
                 });

        // Check overdue invoices at 9 AM on weekdays
        $schedule->command('invoices:check-overdue --send-email')
                 ->weekdays()
                 ->dailyAt('09:00');

        // Cache optimization every 6 hours
        $schedule->command('cache:prune-stale-tags')
                 ->cron('0 */6 * * *');

        // Restart queue workers daily
        $schedule->command('queue:restart')
                 ->daily();

        // Health check every hour
        $schedule->command('system:health-check')
                 ->hourly()
                 ->onFailure(function () {
                     // Send alert on health check failure
                 });
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

## Monitoring Cron Jobs

### Create a monitoring script:
```bash
#!/bin/bash
# Monitor cron jobs and send alerts if they fail

LOG_FILE="/var/log/cron-monitor.log"
ADMIN_EMAIL="admin@your-domain.com"

# Function to log and send alert
log_and_alert() {
    echo "$(date): $1" >> $LOG_FILE
    echo "$1" | mail -s "Cron Job Alert" $ADMIN_EMAIL
}

# Check if scheduler ran in the last hour
if [ $(find /path/to/your/project/storage/logs/laravel.log -mmin -60 | wc -l) -eq 0 ]; then
    log_and_alert "Laravel scheduler may not be running - no recent log entries"
fi

# Check disk space
DISK_USAGE=$(df /path/to/your/project | tail -n1 | awk '{print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    log_and_alert "Disk usage is ${DISK_USAGE}% - consider cleanup"
fi

# Check if application is responding
if ! curl -f http://your-domain.com/health > /dev/null 2>&1; then
    log_and_alert "Application health check failed"
fi
```

Add this monitoring script to your crontab to run every hour:
```bash
0 * * * * /path/to/monitoring-script.sh
```

## Important Notes

1. **Always test cron jobs in staging before deploying to production**
2. **Set up proper logging to track cron job execution**
3. **Monitor disk space and memory usage regularly**
4. **Use absolute paths in cron jobs to avoid path issues**
5. **Set up alerts for failed cron jobs**
6. **Backup your database before running cleanup or archival jobs**
7. **Consider using Laravel's built-in scheduler for most tasks**
8. **Test email notifications from cron jobs to ensure they work**
9. **Keep your server time synchronized with NTP**
10. **Document your cron jobs for future reference**

## Troubleshooting

### Common Issues:

1. **Cron jobs not running**: Check if cron service is active: `systemctl status cron`
2. **Path issues**: Use absolute paths in your cron commands
3. **Permission issues**: Ensure the web server user can execute the commands
4. **Environment variables**: Some cron environments don't load .env files automatically
5. **Time zone issues**: Ensure your server time zone is correct

### Debugging Commands:

```bash
# Check cron service status
systemctl status cron

# Check system logs for cron errors
journalctl -u cron -f

# Test Laravel scheduler
php artisan schedule:run --verbose

# Check cron logs
tail -f /var/log/syslog | grep CRON
```