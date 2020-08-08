# Amazon Magento 2 Integration

### Global Cron setup
+ Comment the cron jobs in `crontab.xml`
+ Add the following jobs in system cron `crontab -e`
  
```php
const CRON_1MINUTE = '* * * * *';
const CRON_5MINUTES = '*/5 * * * *';
const CRON_10MINUTES = '*/10 * * * *';
const CRON_15MINUTES = '*/15 * * * *';
const CRON_20MINUTES = '*/20 * * * *';
const CRON_HALFHOURLY = '*/30 * * * *';
const CRON_HOURLY = '0 * * * *';
const CRON_2HOURLY = '0 */2 * * *';
const CRON_DAILY = '0 0 * * *';
const CRON_TWICEDAILY = '0 0,12 * * *';
```

```bash
*/5 * * * * php bin/magento integrator:amazon:queue:process
0 0,12 * * * php bin/magento integrator:amazon:queue:flush
*/5 * * * * php bin/magento integrator:amazon:queue:sync

0 * * * * php bin/magento integrator:amazon:product:inventory
0 * * * * php bin/magento integrator:amazon:product:price

*/15 * * * * php bin/magento integrator:amazon:order:import
*/15 * * * * php bin/magento integrator:amazon:order:shipment:sync
```

+ Run to install Amazon Module crons:
```bash
 php bin/magento i:a:c:i -d 1
```