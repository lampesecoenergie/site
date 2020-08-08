#Amazon Magento 2 Integration
###In Progress
1. Product Feed Errors Parsing.
    1.1: Parsing XML and adding error on products in a understandable form.
    1.2: Auto fixing the errors on basis of conditions.Suggesting the selller with simplified errors.
2. Queue Feed dependency -> discussion. 
3. Testing:
     2.1 Live order import and shipment testing.
     2.2 Api throttle testing via queing.
     2.3 Configurable product upload.
4. Credentials validation plugin, validate in settings.
6. Product upload: configurable variant -- validate configurable attribute
7. Add ping for timesyncing: https://mws.amazonservices.com/ 

8. Queue Processing should be per min. API call availability should be maintained.
Prepare and Send feed only if API call is available.
If Throttle, save the recovery time and skip.

9. Order import cron add locking mechanism.

###Completed
5. Search and import orders. -- done

