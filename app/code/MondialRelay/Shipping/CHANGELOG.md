### [100.10.2] - 2020-06-26

  * [Fix] Fix pickup block data on some configurations

### [100.10.1] - 2020-02-17

  * [Update] Save pickup address in database. Avoid pickup request on order save.

### [100.10.0] - 2019-06-07

  * [Fix] Pickup removed in checkout on shipping validation instead of input selection

### [100.9.12] - 2019-05-16

  * [Update] LCC delivery added for return
  * [Update] FR added for HOM delivery

### [100.9.11] - 2019-04-05

  * [Fix] Pickup selection in admin for magento >= 2.3.1

### [100.9.10] - 2019-03-13

  * [Fix] Edit order with virtual products
  * [Fix] Compatibility with zopim/zendesk chat

### [100.9.9] - 2019-02-22

  * [Fix] Subtotal amount used for free Shipping calculation

### [100.9.8] - 2019-02-15

  * [Update] Print labels in selected item check order

### [100.9.7] - 2019-02-14

  * [Update] "Label" word translation

### [100.9.6] - 2019-02-05

  * [Fix] Pickup update on third party checkout

### [100.9.5] - 2019-01-18

  * [Fix] PHP 7.2 compatibility
  * [Fix] Disabled pickup for multi-shipping & PayPal Express Checkout
  * [Update] Use component for pickup JS

### [100.9.4] - 2019-01-11

  * [Update] Custom name on label

### [100.9.3] - 2018-12-20

  * [Update] Code style
  * [Update] Mondial Relay menu logic

### [100.9.2] - 2018-12-10

  * [Update] Packaging weight added
  * [Update] Multi-packaging added for 24L and DRI
  * [Fix] Special chars in Pickup name on label creation

### [100.9.1] - 2018-11-07

  * [Update] Deposit file added

### [100.9.0] - 2018-10-22

  * [Update] Mondial Relay Label grid added
  * [Update] Code style
  * [Update] Spanish translation

### [100.8.1] - 2018-07-13

  * [Fix] Plugin CatalogAttributes helper does not exist without compilation

### [100.8.0] - 2018-07-11

  * [Update] Size limitation
  * [Update] Debug mode
  * [Update] Pickup XXL removed
  * [Update] All pickup types in admin checkout
  * [Update] All pickup types for return
  * [Fix] Pickup code on reorder

### [100.7.2] - 2018-07-01

  * [Update] Pickup data API security

### [100.7.1] - 2018-06-29

  * [Update] Create Shipping Label with Magento EE Rma
  * [Update] Configuration split
  * [Update] Compatibility with Magento 2.2.5
  * [Update] Better OSM server
  * [Fix] Return label download

### [100.7.0] - 2018-06-26

  * [Update] Pickup XL and XXL added
  * [Update] Drive added
  * [Update] Map zoom when only one result
  * [Fix] Pickup address on shipping address updated

### [100.6.9] - 2018-06-20

  * [Update] Defines the maximum weight for shipment
  * [Update] Better country configuration
  * [Fix] Useless condition deleted for free shipping calculation

### [100.6.8] - 2018-06-15

  * [Update] WSI3_PointRelais_Recherche to WSI4_PointRelais_Recherche
  * [Update] Defines the number of pickup returned in the response

### [100.6.7] - 2018-06-11

  * [Update] Pickup location update link under address
  * [Update] Test API credentials
  * [Update] Code config prefix
  * [Fix] API configuration by store on label creation

### [100.6.6] - 2018-06-05

  * [Fix] Shipping address same as billing on re-order for pickup

### [100.6.5] - 2018-06-04

  * [Update] Fees by postcode
  * [Update] Spanish translations

### [100.6.4] - 2018-05-29

  * [Fix] Pickup selection on new order

### [100.6.3] - 2018-05-29

  * [Fix] Fix incorrect dependency during compilation

### [100.6.2] - 2018-05-28

  * [Update] Free amount with cart subtotal added
  * [Fix] Pickup selection when items are updated in admin checkout
  * [Update] Pickup auto-selection on re-order

### [100.6.1] - 2018-05-24

  * [Update] OpenStreetMap tile

### [100.6.0] - 2018-05-21

  * [Update] OpenStreetMap integration (alternative to Google Maps)
  * [Update] Return Label improvement
  * [Fix] Fix event manager dependency

### [100.5.2] - 2018-05-16

  * [Fix] Admin ACL
  * [Fix] Translation

### [100.5.1] - 2018-04-19

  * [Update] Better weight conversion
  * [Fix] Pickup list height on mobile with country selection

### [100.5.0] - 2018-04-18

  * [Update] Label Auto creation
  * [Update] Admin pickup selection

### [100.4.0] - 2018-04-05

  * [Update] PSR-2 coding standard
  * [Update] Return label integration
  * [Update] Web API consistency
  * [Update] Compatibility with third party OneStepCheckout
  * [Update] Additional order validation for pickup (better osc integration)
  * [Fix] Small screen integration
  * [Fix] Forced default country for US delivery

### [100.3.13] - 2018-03-14

  * [Update] API pickup order rights
  * [Fix] Current pickup on checkout reload

### [100.3.12] - 2018-03-01

  * [Update] Germany and Austria added for pickup

### [100.3.11] - 2018-03-01

  * [Fix] Shipping validation name

### [100.3.10] - 2018-02-28

  * [Fix] Pickup opening
  * [Fix] WS request when postcode is empty
  * [Update] Spanish translation

### [100.3.9] - 2018-02-26

  * [Fix] Code style
  * [Fix] deprecated instantiation removed
  * [Update] Pop-in style and mobile compatibility
  * [Update] Account configuration by store
  * [Update] Default address configuration if customer has not entered any data

### [100.3.8] - 2018-02-21

  * [Fix] Country and open configuration by store
  * [Fix] US country set by default

### [100.3.7] - 2018-02-15

  * [Fix] Shipping data on virtual order
  * [Update] Do not include Map if Pickup is disabled

### [100.3.6] - 2018-02-07

  * [Fix] API pickup selection
  * [Update] Api method added for retrieve shipping data

### [100.3.5] - 2018-01-23

  * [Update] Automatic Label deletion

### [100.3.4] - 2018-01-22

  * [Update] Shipping Methods sort order added
  * [Update] Readme installation updated

### [100.3.3] - 2018-01-17

  * [Fix] Fix checkout error on customer address update
  * [Fix] Retrieve current selected address for default pickup location
  * [Fix] Fix error when shipping method is not selected

### [100.3.2] - 2017-12-18

  * [Update] Resource Model added
  * [Fix] Fix request with prefixed tables

### [100.3.1] - 2017-11-23

  * [Update] Retrieve default customer shipping address
  * [Update] Configuration added for pickup list opening on click
  * [Update] Controller action refactoring

### [100.3.0] - 2017-11-22

  * [Update] Checkout integration
  * [Update] Api improvement
  * [Update] Header licence

### [100.2.19] - 2017-11-15

  * [Update] Spanish translation added
  * [Update] Translation updated

### [100.2.18] - 2017-10-30

  * [Update] Label API added

### [100.2.17] - 2017-10-14

  * [Update] Better checkout integration
  * [Update] Magento 2.2 compatibility

### [100.2.16] - 2017-08-04

  * [Fix] Setup for split database

### [100.2.15] - 2017-07-19

  * [Fix] Shipping tab for Virtual Order

### [100.2.14] - 2017-06-21

  * [Fix] Quote extension attribute compatibility

### [100.2.13] - 2017-06-21

  * [Fix] Quote extension attribute compatibility

### [100.2.12] - 2017-05-30

  * [Fix] Order creation for virtual order

### [100.2.11] - 2017-05-18

  * [Update] Countries configuration
  * [Update] Configuration style
  * [Update] Tracking link

### [100.2.10] - 2017-05-17

  * [Update] Code style
  * [Update] SOAP improvement

### [100.2.9] - 2017-05-11

  * [Update] Remove installation time information

### [100.2.8] - 2017-05-09

  * [Fix] Template block assignment

### [100.2.7] - 2017-05-09

  * [Update] API configuration

### [100.2.6] - 2017-05-09

  * [Update] New tab for Mondial Relay on order view
  * [Update] No customer address on map

### [100.2.5] - 2017-05-09

  * [Fix] Only country required for Shipping validation
  * [Fix] Useless chars in address Fields
  * [Update] Domain as separated method
  * [Update] Shipping Weight conversion

### [100.2.4] - 2017-04-28

  * [Fix] Third party module compatibility with extension attributes

### [100.2.3] - 2017-04-28

  * [Update] Avoid admin template override

### [100.2.2] - 2017-04-28

  * [Fix] Mixins conflict with other third party modules
  * [Update] CSS updated

### [100.2.1] - 2017-05-02

  * [Fix] Set installation time from admin order

### [100.2.0] - 2017-05-01

  * [Add] First stable release