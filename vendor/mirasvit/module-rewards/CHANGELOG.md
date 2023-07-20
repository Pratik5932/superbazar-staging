# Change Log
## 3.0.57
*(2023-03-15)*

#### Fixed
* PHP 8.2
* Deprecated Functionality: strpos(): Passing null to parameter in Spending/Tier.php
* Call to a member function getSpendAmount() on bool in ApplyDiscountToItemPlugin245

---



## 3.0.56
*(2023-03-02)*

#### Fixed
* Argument value must be type of string, object given. in framework/Filter/Template.php when points expire by cron

---

## 3.0.55
*(2023-03-01)*

#### Fixed
* Changed column names in the admin referral grid
* The referee name and email was not set after signing up
* Existent customer became referee by signing up a referral link
* Magento 2.4.6 returns number of points in currency format
* Class "Magento\Framework\Filter\FilterInput" not found when save rule in admin
* Deprecated Functionality: strpos(): Passing null to parameter #1 in Combine.php

---

## 3.0.54
*(2023-02-01)*

#### Fixed
* Rewards discount total row is added to the cart total when coupon is applied to the shipping amount and points were not spend
* Increased discount applied to the item in checkout
* Cart Rule Actions validated catalog products instead of Cart Items(Mirasvit Sales Rule condition "is Discounted" )

#### Improvements
* Compatibility with M2.4.6

---

## 3.0.53
*(2022-12-12)*

#### Fixed
* Unsubscribe email link leads to a 404 page
* Not valid coupon is applied if Earning rule has an option "Coupon used"
* Points earning for signing up event after account confirmation
* Rewards discount was multiplied by the Magento rules qty for items calculation method
* Rewards discount was multiplied by the Magento rules qty for items calculation method
* Disable displaying separate row total Rewards discount for 'items' calculation method

#### Improvements
* Compatibility with MagestyApps_PaymentFee

---

## 3.0.52
*(2022-11-07)*

#### Fixed
* Enable Fakebook Like button counter 
* 'special_price' attribute worked for non-valid date 
* Guest customer is redirected to login page when tries to access rewards account
* Compatibility with Aheadworks_OneStepCheckout 
* Remove duplicate Rewards Description 

#### Improvements
* Compatibility with Klarna Payment M245

---

## 3.0.51
*(2022-09-29)*

#### Fixed
* Items discount does not apply in m2.4.4 and above for items calculation method
* Deprecated Functionality: str_replace(): Passing null to param
* Trying to access array offset on value of type null Tier.php when cart rule is not configured

#### Improvements
* Added email variables getInvitationLink & getCustomerTier to the order obj 
* Added 'alt' tag to the loader img 

---

## 3.0.50
*(2022-09-05)*

#### Fixed
* A non well formed numeric value encountered in Spend.php for option 'Display points as currency equivalent'
* Foreign key constraint fails mst_rewards_customer_referral_link for guest customer on share page
* Division by zero in Helper/Balance/Earn.php
* Field Inactivity does not exist in table mst_rewards_points_aggregated_hour
* M245 strtotime(): Passing null to parameter #1
* Wrong cells activated upon opening behavior rules

---

## 3.0.49
*(2022-08-18)*

#### Fixed
* klarna/m2-checkout <9.1.5v is no longer supported

---

## 3.0.48
*(2022-08-16)*

#### Fixed
* Rewards discount description overrides coupon discount label for total calculation method
* Undefined variable: earnedBehaviorTransaction 

---

## 3.0.47
*(2022-08-10)*

#### Fixed
* Compatibility with m2.4.5

---

## 3.0.46
*(2022-08-01)*

#### Fixed
* Unsubscribe url doesn't work in the rewards history tab in customer account
* Creating empty purchase for same quote after order placing
* Points earned for the Behavior rule 'Customer places order' are not canceled with order refund

#### Improvements
* Caching calculated product points to avoid performance for product & catalog pages

---

## 3.0.45
*(2022-07-07)*

#### Fixed
* Incorrect purchase is returned from PurchaseRepository by orderId
* afterGetBaseSubtotalWithDiscount returns null for METHOD_ITEMS calculation
* Deprecated Functionality: trim() in RewardsCheckout/Model/Checkout/Rewards.php
* Restored and canceled points are taken into account for the tier calculation
* Earning rule option 'Spend Max' gets only first tier data for setting 'points as currency equivalent'
* Can't find variable: fbLocaleCode for modal product view for Argento theme
* Cannot read properties of undefined (reading 'visibleValue')

---

## 3.0.44
*(2022-05-17)*

#### Fixed
* Error "PayPal gateway has rejected request. The totals of the cart item amounts do not match order amounts"

---

## 3.0.43
*(2022-05-17)*

#### Improvements
* Update mirasvit/module-core dependency
* Compatibility with MSP_DevTools module

#### Fixed
* Rewards amount was not displayed on customer view refund page
* Condition 'discount is used does not work for Spending Rules'

---

## 3.0.42
*(2022-03-29)*

#### Fixed
* Compatibility with Klarna Checkout v9.2.2
* Saving logo in m2.3
* Compatibility with AWS S3 filesystem (tier logo)

---

## 3.0.41
*(2022-03-10)*

#### Improvement
* php8 compatibility

---

## 3.0.40
*(2022-02-14)*

#### Fixed
* Earning Rule Conditions doesn't contain all payment methods
* Minimum order amount was not validated for 'total' calculation method
* Magento coupon is unset when the Spending rule is saved
* Transaction datetime converted the same way as Magento order
* Reward Points number displayed with the currency symbol in the invoice PDF
* The option 'Allow to display maximum number of possible points for configurable products' doesn't take into account 'Spend Max' per rule
* House number and addition weren't added to the shipping address with the Tig PostNL module sipping

---

## 3.0.39
*(2021-11-22)*

#### Improvements
* Quick Data Bar
* PHP 7.1.0+

---

## 3.0.38
*(2021-11-16)*

#### Fixed
* Magento coupon is unset when the Spending rule is saved
* Progress bar displays incorrect progress for the negative tiers calculation
* Balance email was not sent when transaction is created via API

## 3.0.37
*(2021-11-04)*

#### Fixed
* Customer_id parameter uncached 
* Condition 'Lifetime Spent Points' selected all existing transactions of the current customer instead of only spend
* When enabled async sending emails - points are not added to the current purchase due to the order_id saved in the next purchase
* Rewards discount amount was incorrect in credit memo print pdf
* Store Credit amount disaplayed in the Rewards input field in Credit Memo
* Compatibility with Amasty_ShippingTableRates 1.4.12
* Rewards block inputs are disabled in checkout after page load for Rokantheme checkout
* Event 'customer writes a product review' didn't work for the rules with the product attributes


#### Improvements
* Compatibility with Ajax Cart modules
* Added report columns for the 'Customer inactive' and 'Customer refers friend' events

---

## 3.0.36
*(2021-08-20)*

#### Fixed
* Tier calculations do not include rewards discount
* Tiers weren't updated for all the customer groups in the store
* Guest customer is redirected to 404 when press 'Refer Friend' button

#### Improvements
* Compatibility with M 2.4.3
* Added console command to clear rewards purchase table

---

## 3.0.35
*(2021-07-29)*

#### Fixed
* Shipping discount applies twice in the invoice

#### Improvements
* Added Referral Lending page widget
* Added Bss_OneStepCheckout compatibility
* Added new api endpoint getFriendsList
* Added image to the referral invitation email template

---

## 3.0.34
*(2021-06-11)*

#### Fixed
* Removed parameter from API endpoint addReferral
* Number of points wasn't displayed in Earning rule grig in admin

#### Improvements
* Added Refer Friend widget
* Added endpoint sendReferralMessage

---

## 3.0.33
*(2021-06-11)*

#### Fixed
* Added metatitle for the rewards/account page
* Beginner tier customer attribute is not set so not visible in the customers grid in the admin panel
* Points are not updated on the product page for configurable product with dropdown options

#### Improvements
* Stripe Payment compatibility
* Added option for the Rewards menu displaying for the separate customer groups

---

## 3.0.32
*(2021-05-12)*

#### Fixed
* The setting 'Include discount for Earning rules' did not work properly for 'Apply to totals' calculation method
* Call to a member function setChildren() on boolean in RewardsCatalog/Plugin/Product/Type/Configurable.php on product page on frontend if the product is out of stock
* Trying to access array offset on value of type null in EarnPointsStr.php in admin if the rule has empty data
* Wrong totals in creditMamo for the 'items calculation method'
* Email variable customer.name didn't work
* Added multiple index for order_ir in mst_rewards_purchase table to avoid RewardsApi performance issue

#### Improvements
* Added console command and cronjob to refresh report statistic from CLI and by cron
* Compatibility with "Bolt payment gateway integration" v2.21.0

---

## 3.0.31
*(2021-04-21)*

#### Fixed
* Call to a member function getAllIds() on null for the Behavior rules with conditions for categories when place order with paypal and add tracking number in shipping as admin
* Unsupported symbols for the grid notification popup in m24
* Changed description of the 'Earn maximum' field in Earning rule
* Failed to parse time string when saving Spending rule for the different locales in admin

---

## 3.0.30
*(2021-03-30)*

#### Fixed
* Performance issue on Cart and Checkout due to indexes of the mst_rewards_purcase table

---

## 3.0.29
*(2021-03-26)*

#### Fixed
* Styles of social buttons on the account page
* Points are not calculated for the customizable options of the product
* Call to undefined method getRequest for custom controllers
  
#### Improvements
* Added Rewards Logo on the frontend

---

## 3.0.28
*(2021-03-10)*

#### Fixed
* Unique constraint violation found when cancel order in the backend

---

## 3.0.27
*(2021-02-17)*

#### Fixed
* Unable to spend points in the cart/checkout due to order cancellation during payment for some payment methods

---

## 3.0.26
*(2021-02-02)*

#### Fixed
* Compatibility with Klarna Checkout v9.1.5
* Issue when on the twitter popup url is displayed in unicode

---

## 3.0.25
*(2021-01-25)*

#### Fixed
* Added tier name to export.csv for export customer grid

---

## 3.0.24
*(2020-12-30)*

#### Improvements
* Compatibility with Klarna Checkout v9.1.5

#### Fixed
* Points earned in the admin if customer reassigned to the group that is not in the earning rule
* For METHOD_ITEMS 'SpendMaxPoints' includes shipping amount if 'Allow to spend points for shipping charges' is disabled in the module settings

---

## 3.0.23
*(2020-12-28)*

#### Improvements
* Compatibility with klarna/module-core:5.2.4

#### Fixed
* Unexpected Return Statement in update_payment_method.js
* Shipping method dissapears on the backend create order page if this method is enabled on the current website but disabled on the Main website in Sales->Shipping

---

## 3.0.22
*(2020-11-25)*

#### Fixed
* Mobile styles for referral section in customer account 
* Earning of reward points for referrals ignore settings "Approve earned points if order has status" and "Approve earned points on invoice"
* Fixed translation for the phrase 'Checkout now and earn' in cart
* Issue when the Rewards extension reset selected addresses for multi shipping
* Added event Prefix to the Transaction Model
* Earning points calculated with tax for non-tax addresses
* Shipping address is rewritten to billing in the checkout
* In Porto theme Rewards link is displayed with the number of earned points for the non-loggedin customer
* Wrong calculation totals Shipping tax for Credit Memo 

---

## 3.0.21
*(2020-10-22)*

#### Fixed
* Issue when earning points notification is shown on empty cart
* Error "Cannot apply Reward Points. The shipping address is missing
* Error in catrt "The shipping method is missing. Select the shipping method"

#### Improvements
* Added support for composer 2

---

## 3.0.20
*(2020-10-10)*

#### Fixed
* Fixed issue when earning points notification is shown on empty cart page
* Added description for Transaction inactive Email notification
* Fixed issue when 'Use maximum' checkbox in cart couldn't be unchecked
* Call to undefined method Rewards\Service\ShippingService::getBas
* Undefined variable when apply pointseRewardsShippingPrice()

## 3.0.19
*(2020-10-02)*

#### Fixed
* Points displaying for the default selected Bundle product
* Error "Call to a member function getEntityId() on null in Rewards/Model/Earning/Rule/Condition/Customer.php"
* Compatibility with Mageplaza_CurrencyFormatter
* Removed cache tags from Rewards Transaction model
* Facebook connect id to avoid csp_whitelist blocking due to identical FB IDs(cause FB is not define)
* Reward Points discount does not remove from the totals after cancelation in the backend order

---

## 3.0.18
*(2020-09-07)*

#### Fixed
* Time of expiration email sending

---

## 3.0.17
*(2020-08-31)*

#### Fixed
* Compatibility with Aheadworks_AdvancedReviews module
* JS error when editing rule conditions/actions
* An incorrect points number in the cart and the checkout rewards message

---

## 3.0.16
*(2020-08-26)*

#### Improvements
* Added option "Spending Discount calculating method"
* Added ability to track referral guest orders + API endpoint V1/rewards/referral/addGuestReferral

#### Fixed
* Compatibility with AfterPay Payment
* Order is not bound to the customer who registered during OnepageAmastyCheckout

---


## 3.0.15
*(2020-08-04)*

#### Improvements
* Added option "Spending Discount calculating method"
* Support of Magento 2.4

#### Fixed
* Displaying of the refunded rewards amount in credit memo
* Compatibility with AfterPay Payment
* Updated migration section
* Displaying of earning points for guest customer

---

## 3.0.14
*(2020-06-03)*

#### Fixed
* Compatibility with WebShopApps_MatrixRate (Error "infinite loop detected")
* Compatibility with MageWorx_StoreLocator (Error "infinite loop detected")
* Earning rule's condition "Coupon Used" when some points were spent

#### Improvements
* Added option "Apply Spending Points Discount After Tax"
* Added validation for tier fields in Earning rules

---

## 3.0.13
*(2020-05-27)*

#### Fixed
* Limited Klarna Checkout version (7.0.0)
* Points calculations for "Apply Customer Tax = After Discount" and "Include tax for Spending rules = true"
* Error "Class Mirasvit\Rewards\Model\Api\ProductPoints does not exist"

---

## 3.0.11
*(2020-05-19)*

#### Fixed
* Compatibility with Aheadgroups_Ordereditor
* Call to undefined method Mirasvit\Rewards\Helper\Behavior::processRule() in RewardsBehavior/Observer/EarnOnPushNotificationSignup.php
* Invalid argument supplied for foreach() in vendor/magento/module-sales-rule/Model/Rule/Condition/Product/Found.php

---

## 3.0.10
*(2020-05-16)*

#### Fixed
* Wrong calculation of earn points for bundle products with coupon discount
* API endpoint "/V1/rewards/products/points/get"

---

## 3.0.9
*(2020-05-08)*

#### Fixed
* Display of points for ajax loaded blocks
* Error "Object of class Region could not be converted to string"
* Issue when coupon discount display in the rewards totals
* Validation of actions in the cart rules
* Points calculations when options "Allow to spend points for shipping charges" and "Include tax for Spending rules" are disabled

---

## 3.0.8
*(2020-05-01)*

#### Fixed
* Notice: A non well formed numeric value encountered in Mirasvit/Rewards/Helper/Balance/SpendCartRange.php
* Error "PayPal gateway has rejected request. The totals of the cart item amounts do not match order amounts" for multi currency cart
* An issue when orders in status "Processing" do not include in the tier progress
* Compatibility with Rokanthemes_OpCheckout https://3.basecamp.com/4292992/buckets/14246564/todos/2612368111
* Compatibility with Magento 2.3.5

---

## 3.0.7
*(2020-04-10)*

#### Fixed
* Made customer attribute "rewards_subscription" optional

#### Improvements
* Added referral api endpoints:
  - added API endpoint /V1/rewards/mine/referalCode
  - added API endpoint /V1/rewards/mine/addReferral
  
---

## 3.0.6
*(2020-04-03)*

#### Fixed
* Issue when customer conditions of earning rules break product page
* Compatibility with Amasty One Step Checkout v2.10.1
* Error "Something went wrong: Please check the shipping address information ..." when the Infomodus_Upsap extension enbled https://3.basecamp.com/4292992/buckets/14246564/todos/2531864294
* Points calculations for modules that reset quote grand total
* Display of earning points on product page when "Customer Earning Style" = "Give X points for every Z quantity"

---

## 3.0.5
*(2020-03-24)*

#### Fixed
* Action validation for earning rules when "Customer Earning Style" = "Give X points to customer"

---

## 3.0.4
*(2020-03-19)*

#### Fixed
* Error "Exception #0 (Exception): Warning: Division by zero in vendor/mirasvit/module-rewards/src/Rewards/Model/Total/Creditmemo/Discount.php"

---

## 3.0.3
*(2020-03-13)*

#### Fixed
* Error 404 for rewards css file when option "Force to apply styles" is enabled
* Compatibility with the Customweb_DocDataCw extension https://3.basecamp.com/4292992/buckets/14246564/todos/2471205840
* Issue "Reward points do not apply during order creation in admin" https://3.basecamp.com/4292992/buckets/14246564/todos/2486617910
* Earning rules validation for Rewards product block https://3.basecamp.com/4292992/buckets/14246564/todos/2487580335

---

## 3.0.2
*(2020-03-03)*

#### Fixed
* Error during import "Please enter a correct entity model"
* Duplicating of the export "CSV" button in the transaction table
* Error "Class RewardsDiscount does not exist" when use points with Klarna Payment
* Fatal error: Uncaught TypeError: strlen() expects parameter 1 to be string, object given in vendor/magento/module-sales-rule/Model/Quote/Address/Total/ShippingDiscount.php"
* Update of shipping methods when "Minimum Order Amount" is set for Free shipping method
* Fixed "Notice: Undefined variable: purchase" for soap searchCriteria salesOrderRepositoryV1  

---

## 3.0.0
*(2020-02-12)*

#### Fixed
* JS errors on guest checkout
* Change points calculations when enabled "Minimum Order Amount"
* Rewards balance amount value during customer export

#### Improvements
* Code refactoring. Divided the Rewards module to several submodules.
* Removed earning product rules.
* Added customer conditions to the spending rules 

---

## 2.3.46
*(2020-01-16)*

#### Fixed
* Payment method selection was lost after totals' reloading
* Date saving for different locales
* Display of points for out of stock products
* Display od decimal points for the backend earning rules

---

## 2.3.45
*(2020-01-02)*

#### Fixed
* Condition "Special Price"
* Display of reward points for product's options

#### Improvements
* Moved rewards social block above product image in the mobile view

---

## 2.3.43
*(2019-11-28)*

#### Fixed
* Earning points calculations

---

## 2.3.42
*(2019-11-26)*

#### Fixed
* Earning points calculations

---

## 2.3.41
*(2019-11-22)*

#### Improvements
* Added ability to display cart rule's points on product page

---

## 2.3.40
*(2019-11-22)*

#### Fixed
* Points calculations for shipping and tax applied after discount

---

## 2.3.39
*(2019-11-20)*

#### Improvements
* Compatibility with Bambora Online v1.3.1

---

## 2.3.38
*(2019-11-13)*

#### Fixed 
* Formatting of earned points in credit memo
* Points update for api requests

---

## 2.3.37
*(2019-11-12)*

#### Fixed 
* Added customer validation for get balance endpoint
* Remove requirements for searchCriteria in API endpoints
* Getting of min shipping amount for different websites

#### Improvements
* Added rewards balance to the customer grid

---

## 2.3.36
*(2019-11-08)*

#### Fixed 
* Calculations for Credit Memo when Adjustment Fee wa used

---

## 2.3.35
*(2019-10-31)*

#### Fixed 
* Saving reward totals in Checkout sort totals order config for m2.3.3
* Rewards form does not show in the backend order creation form
* Points calculations on payment update event

#### Improvements
* Added new APIs for tiers, spending and earning rules

---

## 2.3.34
*(2019-10-21)*

#### Fixed 
* Rewards calculations for creditmemo without returned shipping

---

## 2.3.33
*(2019-10-15)*

#### Fixed 
* Error "Invalid Method Mirasvit\Rewards\Block\Buttons::isOneActive" since 2.3.32

---

## 2.3.32
*(2019-10-11)*

#### Fixed 
* Points calculations for option "Allow to display product points as currency equivalent"
* Deleted Google+ event from behavior rules
* "Minimum Order Amount" for free shipping method when reward points were used
* Validation for the field 'For each spent X points' 

---

## 2.3.31
*(2019-09-30)*

#### Fixed 
* Update of earn points in earning message on cart page
* Displaying of active dates 

---

## 2.3.30
*(2019-09-28)*

#### Fixed 
* Earning points for zero total order

---

## 2.3.29
*(2019-09-27)*

#### Fixed 
* Error "Exception #0 (Exception): Field order_earn not exists in table mst_rewards_points_aggregated_hour"
* Points for newsletter subscription for mailchimp/mc-magento2
* Points calculations for "Include discount for Earning rules"
* Rounding points up

---

## 2.3.28
*(2019-09-24)*

#### Fixed 
* Points calculations for Catalog Prices - Including Tax, Apply Customer Tax - After Discount
* Uncaught Error: Call to a member func on boolean in AddVarsToEmail.php for invoice mail
* Points calculations for partial creditmemo

#### Improvements
* Added "Order Subtotal" condition to behavior earning rules

---

## 2.3.27
*(2019-09-13)*

#### Fixed 
* Tier error during shpping creation

---

## 2.3.26
*(2019-09-12)*

#### Fixed 
* Error "Field order_earn not exists in table mst_rewards_points_aggregated_hour" since 2.3.35

---

## 2.3.25
*(2019-09-12)*

#### Fixed 
* Points displaying for out-of-stock products
* Points information for partial creditmemo
* Reports

---

## 2.3.24
*(2019-09-04)*

#### Fixed 
* Compatibility with MageWorx DeliveryDate extension
* Incorrect points discount when PayPal payment method was used

#### Improvements
* Added searchCriteria to transaction API
* Added ability to give points for RMA

---

## 2.3.23
*(2019-08-15)*

#### Fixed 
* Country condition for earning rules
* Translation for expiration date
* Display points for tier prices on product page
* Issue when rewards form does not show in cart/checkout

---

## 2.3.22
*(2019-08-07)*

#### Fixed 
* Compatibility with Amasty_ShippingTableRates
* Added condition "Additional Payment Method" to earning cart rules
* Prevent update of purchase data after order was created
* Error "Call to a member function getId() on array in Rewards/Model/Cron/Tier.php"
* Issue when total's spend row hides on cart update
* Active From/To fields saving for all rules
* Error "ReferenceError: rewardsCurrentUrl is not defined"

#### Improvements
* "activate transaction" cron task

---

## 2.3.21
*(2019-07-18)*

#### Fixed 
* Possible division by zero
* Points not loading on catalog page if redis enabled
* Translation for product page

---

## 2.3.20
*(2019-07-12)*

#### Fixed 
* Compatibility with extension Bss OrderAmount
* Calculation for fixed amount steps of earning rules
* Call to a member func on bool on the product page when Amasty_CommonRules is enabled

#### Improvements 
* Display of product points using ajax

---

## 2.3.19
*(2019-06-25)*

#### Fixed 
* Issue when Klarna payment method disappears after points were applied
* Issue when unnecessary <br> added to behavior notification emails
* Behavior rule condition "Is Referral"

---

## 2.3.18
*(2019-06-14)*

#### Fixed 
* Issue for Magento EE v2.2.5 when rewards discount is reset on place order (apply customer tax before discount)

---

## 2.3.17
*(2019-06-12)*

#### Fixed 
* Points amount on product page for tier price
* Earn points do not display in checkout if spend points is not allowed 
* Condition "Discount Used" in spending rules

#### Improvements 
* Compatibility with Klarna Checkout

---

## 2.3.16
*(2019-05-31)*

#### Fixed 
* Points discount for "tax applied after discount"
* Facebook Like button loading for m 2.3.1 and remove private content for social buttons

---

## 2.3.15
*(2019-05-27)*

#### Fixed 
* Points rounding on product page
* Values of condition "Payment Method" for earning rule

---

## 2.3.14
*(2019-05-24)*

#### Fixed 
* Points rounding on product page
* Points name on customer order view page

#### Improvements 
* Added {{order_increment_id}} to earning rule history message

---

## 2.3.13
*(2019-05-20)*

#### Fixed 
* Compatibility with Amasty Shipping Rates
* Applying of reward points by "enter" button in checkout

---

## 2.3.12
*(2019-05-15)*

#### Improvements 
* Compatibility with Klarna extension v6.1.0, Klarna Core v5.1.0
* Display format of earned points in backend on order view page

#### Fixed 
* "Minimum Order Amount" does not consider Rewards discount
* Display of rewards discount for multi currency stores
* Issue when messages on product page hide very fast

---

## 2.3.11
*(2019-05-03)*

#### Improvements 
* Added ability to delay usage of rewards points
* Improve tier update cron job

---

## 2.3.10
*(2019-04-23)*

#### Fixed 
* Allow to use percent in monetary step of spending rule

---

## 2.3.9
*(2019-04-19)*

#### Fixed 
* Remove rewards expiring notification subscription from Newsletter Subscription page (left only on My Reward Points > History page)

---

## 2.3.8
*(2019-04-17)*

#### Fixed 
* Label "Rewards Discount" overrides coupon discount label in cart
* Points does not applies to Grand Total if tax does not include in spending rules

---

## 2.3.7
*(2019-04-10)*

#### Fixed 
* Display of tier progress bar for rtl theme
* Compatibility with m 2.3.1
* Display Rewards discount in backend order creation form

---

## 2.3.6
*(2019-03-27)*

#### Fixed 
* Points calculations for min/max price of bundle product
* Issue when condition "Number of reviews" includes pending reviews
* Spend maximum calculates incorrect
* Remove unnecessary rewards calculations

---

## 2.3.5
*(2019-03-21)*

#### Fixed 
* Compatibility with Magecomp_Surcharge
* Added Rewards discount to "Checkout Totals Sort Order"
* Display of Rewards discount in backend print pdf  

---

## 2.3.4
*(2019-03-19)*

#### Fixed 
* Display of rewards discount in backend and emails
* Rewards information for SOAP API order request

---

## 2.3.3
*(2019-03-15)*

#### Fixed 
* Display of rewards discount for order

---

## 2.3.2
*(2019-03-14)*

#### Fixed 
* Translation for the date range in transaction table
* Compatibility with Aheadworks_OneStepCheckout

#### Improvements
* Added new api:
- /V1/rewards/products/points/get
- /V1/rewards/products/points/multiplicity

---

## 2.3.1
*(2019-03-12)*

#### Fixed 
* Rewards discount does not includes in Paypal total 
* error "Notice: Undefined index:  int NOT NULL DEFAULT 0" for Magento 2.3.x
* Js error "Mirasvit_Rewards/js/product/view.min.js net::ERR_ABORTED 404 (Not Found)" on backend order creation page

---

## 2.3.0
*(2019-03-07)*

#### Fixed 
* Issue when rule save wrong data if message starts from variable
* Condition "Coupon used is no" does not work in spending rules
* Js error on product page for custom product types
* Points calculations on paypal preview checkout

#### Improvements
* Moved rewards discount to totals
* Compatibility with AwStoreCredit and AmastyGift extension
* Compatibility with Magecomp Paymentfee v1.0.5
* Added new api:
    - V1/rewards/product/points

---

## 2.2.34
*(2019-02-07)*

#### Fixed 
* Points calculations for bundle product with special price
* Fixed points calculations for bundle product with special price
* Added new api:
    - V1/rewards/:cartId/apply/:pointAmount

#### Improvements
* Added ability to save "Logo" of Tier per storeview
* Added ability to save "Front Name" of Spending Rule per storeview

---

## 2.2.33
*(2019-02-06)*

#### Fixed 
* Disable coupon show success message after applying
* Bundle description does not update when no product rule
* Compatibility with Amasty Free Gift
* Product amount in minicart does not update correctly
* Added new api:
    - V1/rewards/transactions
    - V1/rewards/purchase/:orderId
    - V1/rewards/balances
    - V1/rewards/balances/:customerId
    - V1/rewards/mine/purchase/:orderId

---

## 2.2.32
*(2019-01-23)*

#### Fixed 
* Update Facebook Like script

---

## 2.2.30
*(2019-01-22)*

#### Fixed 
* Encoding in field min/max for spending rule(percent amount applies as a number)
* Validation of min/max for spending rule

---

## 2.2.29
*(2018-12-26)*

#### Fixed 
* Error when session stores in Redis and rewards notification returns error message 
* Points calculations for earning rule 100:100
* Tier points calculates incorrect on Magento 2.2.7
* Compatibility with Reward Points, Magecomp Surcharge and Aheadworks Store Credit 

#### Improvements
* Added ability to upload logo to tier
* Moved rewards shipping discount from item to address shipping discount

---

## 2.2.28
*(2018-12-12)*

#### Fixed 
* Error on cart page "Cannot read property 'replace' of undefined"
* Compilation error on Magento 2.1.x
* Images in tier description

---

## 2.2.27
*(2018-12-10)*

#### Fixed
* Customer created from checkout get points(Product Rule) for order
* Points calculations for tax based on "Unit Price"
* Reward notification display wrong amount of earned points
* Related products on product page have the same amount of points as main product
* Issue when points do not show for product with price less $1 and qty more then 1
* HTML tags in earning message on cart page

#### Improvements
* Added translation for earning rule "Display Name"

---

## 2.2.26
*(2018-12-04)*

#### Fixed
* Some minicarts do not update products amount when product was removed from cart page
* Wrong number of referrals on account page
* Missing earning rule conditions
* Compatibility with Reward Points, Magecomp Surcharge and Aheadworks Store Credit
* Issue when Cart rule condition "Total Items Quantity" does not work
* Unable to export customer grid with tiers

---

## 2.2.25
*(2018-11-29)*

#### Fixed
* Issue when tier calculation process includes spend points
* Compatibility with Magento 2.3.0

---

## 2.2.24
*(2018-11-27)*

#### Fixed
* Unable to use "Zero Subtotal Checkout" with Reward Points(started from 2.2.20 version)
* Earned points do not cancel on order cancellation
* Email translation variables
* Issue "tier submenu Delete does not work"
* Issue when reward points do not calculate correct for configurable product options with minimum qty greater then 1
* Issue when points do not change on catalog page for different options

#### Improvements
* Added ability to translate product rule notification

---

## 2.2.23
*(2018-11-22)*

#### Fixed
* Issue when rewards discount does not apply to totals
* Issue with the incorrect points calculating for the configurable product options on the catalog page

---

## 2.2.22
*(2018-11-21)*

#### Fixed
* Points calculations on product page when tier and special prices are set
* Points calculations on product page when catalog price rule price displays

---

## 2.2.21
*(2018-11-19)*

#### Fixed
* Tier update events triggered only for the first update
* Points calculation on product page after update qty amount
* Fixed error "Exception message: Notice: Undefined index: 1.0000" during customer registration

#### Improvements
* Compatibility with MageComp Surcharge
* Compatibility with Aheadwords Store Credit

---

## 2.2.20
*(2018-11-13)*

#### Fixed
* Notice: Undefined offset ... Rewards/Observer/BundlePriceConfig.php
* Compatibility with Faonni_Price
* Unnecessary loading of Facebook script on product page
* Incorrect transaction amount for expiration emails.
* Wrong amount of points for product tier price on product page
* Option "based on sum of spend $" does not take into consideration value of option "take into account only last"

#### Improvements
* Added calculation options for tier upgrade process based on spend amount of $
* Compatibility with Amasty Gift Cart. Wrong max allowed points.
* Added events to behavior rule: "Customer tier up" and "Customer tier down"

---

## 2.2.19
*(2018-10-30)*

#### Fixed
* Compatibility with Faonni_Price extension
* Error "fjs does not exist"
* Tax calculation for bundle products

#### Improvements
* API. Added "created_at" field to transactions response
* API. Added min/max points information to totals info

---

## 2.2.18
*(2018-10-29)*

#### Fixed
* Points calculation when rule limited with maximum
* Added option "Show social Button block on category page"
* Fatal error for out of stock configurable products

---

## 2.2.17
*(2018-10-26)*

#### Fixed
* Compatibility with some 3rd party extensions, mini-cart is not updated with main cart
* API. Added rewards information to totals

---

## 2.2.16
*(2018-10-25)*

#### Fixed
* Points calculations for webapi
* Calculation points for simple product's options

---

## 2.2.15
*(2018-10-23)*

#### Fixed
* Points calculations on product page for advanced prices
* "Application ID" does not display after save

#### Improvements
* Added new API:
    - GET /V1/rewards/mine/balance
    - GET /V1/rewards/mine/transactions
    - POST /V1/rewards/mine/apply

---

## 2.2.14
*(2018-10-17)*

#### Fixed
* Rounding for Earning points

---

## 2.2.13
*(2018-10-12)*

#### Fixed
* ReferenceError: rewardsFacebookApiVersion is not defined
* "Subtotal" condition in rules
* Points calculation for multi currency store for bundle and configurable products
* Fixed earning points calculations for option "Apply Customer Tax"
* Amasty Shipping Rates do not show in backed during order creation
* Error "Missing argument 1 for Mirasvit\Rewards\Model\Spending\Rule::getSpendPoints()"
* Tier description and emails for storeview
* Tier description for storeview

---

## 2.2.11
*(2018-09-26)*

#### Improvements
* Added ability to switch between tiers based on sum of spent $
* Added option "After login to account redirect a customer to My Reward Points section"
* Added validation for block modifications
* Added condition "payment method" to 2.2.6(Magento removed it from conditions)

#### Fixed
* Points on product page displays according to current currency instead of base currency

---

## 2.2.10
*(2018-09-11)*

#### Fixed
* Installation
* Styles for option "Force to apply styles"
* Compatibility with Ebizmarts_SagePaySuite
* Error "Call to a member function getID() on null in Service/Customer/Tier.php:169"

---

## 2.2.9
*(2018-08-31)*

#### Fixed
* Fatal error: Uncaught Error: Cannot instantiate interface Mirasvit\Rewards\Api\Repository\TierRepositoryInterface ..."

---

## 2.2.8
*(2018-08-29)*

#### Fixed
* Newsletter unsubscribe does not deduct points
* Displaying of Share and Referral tabs in account

#### Improvements
* Added social buttons on category page

---

## 2.2.6
*(2018-08-28)*

#### Fixed
* Sending multiple emails using the TransportBuilder class causes an Zend_Mail exception
* Update FB API version to 3.1
* Points spending style "Fixed"
* Points calculations when earn points approved on status "Pending" and spend points less then "For each spent X points"
* Actions of Cart Rules

#### Improvements
* Added FB API version number to config
* Added "All Groups" to rule settings

---

## 2.2.5
*(2018-08-17)*

#### Fixed
* Displaying of Rule's history message for multiple store view

---

## 2.2.4
*(2018-08-16)*

#### Fixed
* Ability to save history and email messages for Behavior Rules for store view
* Product review message
* Social buttons
* Backend create order error - "Warning: Division by zero"
* Tax excluded twice on product page

---

## 2.2.3
*(2018-08-08)*

#### Fixed
* Issue with newsletter subscription by cron tier

---

## 2.2.2
*(2018-08-01)*

#### Fixed
* Compilation error
* Customer attribute "Rewards Tier" is required

---

## 2.2.0
*(2018-07-31)*

#### Feature
* Tiers

#### Fixed
* Issue when "Minimum Order Amount" was ignoring
* Issue when rule "Display Name" showing for all groups

---

## 2.1.34
*(2018-07-20)*

#### Fixed
* Added points for the order when customer created an account at the end of checkout 

---

## 2.1.33
*(2018-07-09)*

#### Fixed
* Displaying points for bundle product
* Guest address is not saving in checkout

---

## 2.1.32
*(2018-07-03)*

#### Fixed
* Points calculations for Newsletter Subscription
* Points for tier price on product page
* Compatibility with other Twitter plugins

---

## 2.1.31
*(2018-06-07)*

#### Fixed
* Product page error "Uncaught SyntaxError: Unexpected end of JSON input"
* Expiration email sending
* Points calculations for multi currency stores

---

## 2.1.30
*(2018-05-18)*

#### Improvements
* Added api for transactions

---

## 2.1.29
*(2018-05-04)*

#### Fixed
* Cached social block does not display "Pin it" button
* Compatibility with Aheadworks_OneStepCheckout
* Referral link for stores in subfolder

#### Improvements
* Added option that allows to display pending rewards transactions
* Added Prices to product rule
* Added "Lifetime Spent Points" condition to behavior rules

---

## 2.1.28
*(2018-04-26)*

#### Fixed
* Compilation error

---

## 2.1.27
*(2018-04-25)*

#### Fixed
* Date filter does not work in transaction grid
* Unable to add points in backend to several customers
* Call to a member function getEntityId() on null

---

## 2.1.26
*(2018-04-13)*

#### Fixed
* Issue when inactive pariod does not save 
* Error with message "There is no information about associated entity type "customer_group""
* Color does not change on product page
* Unnecessary calculations for bundle products

#### Improvements
* Added option that adds Rewards style to pages if theme ignore it

---

## 2.1.25
*(2018-04-03)*

#### Fixed
* Behavior rules with order conditions

---

## 2.1.24
*(2018-03-16)*

#### Fixed
* Calculation of minimum points
* Calculation of earning points for virtual cart
* Calculation of spending points for option "Include tax for Spending rules"
* Compatibility with Swissup Firecheckout

---

## 2.1.23
*(2018-03-16)*

#### Fixed
* Report Plugin

---

### 2.1.22
*(2018-03-12)* 

#### Fixed
* Uncaught TypeError: Argument 1 passed to Magento\Quote\Model\Cart\Totals::setExtensionAttributes() must be an instance of Magento\Quote\Api\Data\TotalsExtensionInterface, instance of ...

---

### 2.1.21
*(2018-03-07)* 

#### Fixed
* Miltiorders for one cart

---

### 2.1.20
*(2018-03-07)* 

#### Fixed
* Fixed issue with incorrect email sender in multistore configuration

---

### 2.1.19
*(2018-03-06)*

#### Fixed
* DI Compile (reports module)

---

### 2.1.18
*(2018-03-05)*

#### Improvements
* Allow to earn points for joining affiliate program (Mirasvit_Affiliate)

---

### 2.1.17
*(2018-03-01)*

#### Fixed
* Miltiorders for one cart
* Compatibility with Taxjar SalesTax module
* Points are not assigned by "sign up" rule for Magento 2.2.2

---

### 2.1.16
*(2018-02-20)*

#### Fixed
* Fix error during setup:di:compile

---

### 2.1.15
*(2018-02-19)*

#### Fixed
* Incorrect calculation of base discount (Grand Total (Base) and Grand Total (Purchased) are different)
* Add customer conditions to cart and product earning rules

---

### 2.1.14
*(2018-02-09)*

#### Fixed
* issue with wrong report version requirement

---

### 2.1.11
*(2018-02-09)*

#### Fixed
* Error "Cannot read property 'sectionLoadUrl' of undefined" that appear on slow connection in checkout
* Compatibility with Amasty checkout
* Error "The requested Payment Method is not available" for checkout with Grand Total equal to zero
* Fixed magento 2.2.2 issue https://github.com/magento/magento2/issues/12993 'Argument 1 passed to Magento\Quote\Model\Cart\Totals::setExtensionAttributes() must be an instance of Magento\Quote\Api\Data\TotalsExtensionInterface, instance of Magento\Quote\Api\Data\AddressExtension given'
* Added a formkey and max number of allowed invites to referal invitaion form


---

### 2.1.10
*(2018-01-31)*

#### Fixed
* Points are not calculating correctly when changing shipping in cart 

---

### 2.1.9
*(2018-01-23)*

#### Fixed
* In some cases, incorrect reward points discount calculation. Module rounds discount amount to integer and does not allow to have a fractional discount amount. Affects versions from 2.1.6.
* Registration rule is triggered on customer saving. If customer was created before registration rule and customer does not have transaction for this rule he takes points for registration rule on reset password
* In some cases Twitter returns "Connection timed out"

---

### 2.1.8
*(2018-01-05)*

#### Fixed
* Issue when rewards section was updated twice

---

### 2.1.6
*(2017-12-27)*

#### Fixed
* Social buttons on the account page were incorrectly displayed
* In some cases, when coupon and points were used at the same time, order total was negative

#### Improvements
* Integration with Plumrocket SocialLoginPro

---

### 2.1.5
*(2017-12-20)*

#### Fixed
* Fixed an issue with slow category page load time. If a category page has a lot of configurable products with many sub-products, it took a long time to load the page.

---

### 2.1.4
*(2017-12-19)*

#### Fixed
* In Magento 2.2.2, if customer attempts to register, he sees the error "PHP Fatal error:  Uncaught Error: Call to a member function getId() on null in .../app/code/Mirasvit/Rewards/Observer/BehaviorCustomerRegisterSuccess.php:64"

### 2.1.2
*(2017-11-30)*

#### Improvements
* Compatibility with Magento 2.2.1 

---

### 2.1.1
*(2017-11-28)*

#### Improvements
* Compatibility with Aheadworks onepagecheckout

---

### 2.1.0
*(2017-11-20)*

#### Fixed
* Points calculation on the bundle product page
* Code improvements
* Translation

---

### 2.0.18
*(2017-11-15)*

#### Fixed
* Transaction grid
* Earning points for sign up events
* Styles for social buttons

---

### 2.0.17
*(2017-11-02)*

#### Fixed
* Issue with bundled price observer

---

### 2.0.16
*(2017-10-25)*

#### Improvements
* Added Base Price, Final Price and Special Price to spending rules
* Added condition "Discount Used" to spending rules

#### Fixed
* Rules assigning to website

---

### 2.0.15
*(2017-10-18)*

#### Improvements
* Added referral group to behavior rule condition for event "Customer signs up in store"

#### Fixed
* Rules assigning to websites

---

### 2.0.14
*(2017-10-13)*

#### Fixed
* Fixed twitter url
* Translation

---

### 2.0.12
*(2017-10-10)*

#### Improvements
* Social button

---

### 2.0.11
*(2017-10-09)*

#### Fixed
* Date validation
* Points for "Not Visible Individually" products
* Tax calculation
* Acl for "Transaction"

---

### 2.0.10
*(2017-09-28)*

#### Improvements
* Magento 2.2 Compatibility Fix
* Allowed to change Customer's Group on Behaviour Rule triggering
* Added Is Referee condition to Behaviour Rule
* Improved documentation

### 2.0.9
*(2017-09-25)*

#### Improvements
* Added earning rule of behavior type for subscription on push notification

#### Fixed
* Fixed an issue with actions rule

---

### 2.0.8
*(2017-09-21)*

#### Fixed
* Bugs

---

### 2.0.7
*(2017-09-19)*

#### Improvements
* Rewritten documentation

---

### 2.0.6
*(2017-09-18)*

#### Improvements
* Added conditions "Coupon Used" and "Coupon Code" to Spending Rule and Cart Earning Rule

#### Fixed
* Solved XSS issue
* Reward Points for Shipping Tax

---

### 2.0.5
*(2017-09-11)*

#### Fixed
* Single spending rule with payment method
* Compatibility with Magento 2.2.0rc

---

### 2.0.4
*(2017-09-06)*

#### Fixed
* Points displaying on product page

---

### 2.0.3
*(2017-08-31)*

#### Fixed
* "Customer signs up in store" for checkout
* Points for orders mass actions
* Rule saving for multistore
* Fixed loading of unnecessary social scripts

---

### 2.0.2
*(2017-08-21)*

#### Fixed
* Fixed referral sender for multistore

---

### 2.0.1
*(2017-08-18)*

#### Fixed
* Notification rule saving

---

### 2.0.0
*(2017-08-16)*

#### Improvements
* Refactoring

---

### 1.1.49
*(2017-08-16)*

Final version for Magento 2.0.x

---

### 1.1.46
*(2017-08-03)*

#### Fixed
* Referral sign up event

---

### 1.1.45
*(2017-07-21)*

#### Fixed
* Updating of Reward points on product view page

---

### 1.1.44
*(2017-07-18)*

#### Fixed
* Behavior rules event "Customer signs up in store"
* Translation

---

### 1.1.43
*(2017-07-12)*

#### Fixed
* Different issues with Behavior Rules

#### Improvements
* Added option that allows to include/exclude discount from Earning rules
* Added rewards variables (getRewardsEarnedPoints(), getRewardsSpentPoints(), getRewardsSpentAmount()) to email templates of order, invoice, creditmemo

---

### 1.1.42
*(2017-06-30)*

#### Fixed
* Different issues with Earning Rules 

---

### 1.1.40
*(2017-06-26)*

#### Fixed
* Calculation of points for Spending Rules with "Spend minimum" 

---

### 1.1.37
*(2017-06-14)*

#### Fixed
* Prevent errors during refresh statistic 

---

### 1.1.36
*(2017-06-13)*

#### Fixed
* Rule conditions

---

### 1.1.35
*(2017-06-12)*

#### Fixed
* Displaying of Reward points for grouped products

#### Improvements
* Displaying of spending points in order in backend

---

### 1.1.34
*(2017-06-07)*

#### Fixed
* Rule conditions
* Points cancelation in backend order
* Translation for transaction's auto comments.

#### Improvements
* Added option that allows to control how to round earning points

---

### 1.1.33
*(2017-05-25)*

#### Fixed
* Calculation of used points
* Compatibility with Mageplaza one step checkout
* Calculation of Earning Product Rule for discount

#### Improvements
* Added option that allows to display the highest possible amount of points for configurable product

---

### 1.1.32
*(2017-05-06)*

#### Fixed
* Composer php

---

### 1.1.31
*(2017-05-03)*

#### Fixed
* Default value for "Subscription to Points Expiring Notification"

---

### 1.1.30
*(2017-04-18)*

#### Improvements
* Added ability to unsbcribe from points expiring notification
* Added "Points spending style" option to Spending Rule
* Allow to use percent in "Customer receive Y discount" field in spending rule

---

### 1.1.29
*(2017-04-06)*

#### Improvements
* Compatibility with PSP MultiSafepay

#### Fixed
* "Sign Up for Newsletter" rule for guest

---

### 1.1.28
*(2017-03-17)*

#### Fixed
* Multi store support for email templates

---

### 1.1.27
*(2017-03-09)*

#### Fixed
* Reports

---

### 1.1.25
*(2017-03-06)*

#### Improvements
* Reports

---

### 1.1.24
*(2017-02-20)*

#### Fixed
* Moved "Display options" settings from global to store view settings
* Renamed option "Show rewards points on frontend" to "Show rewards points on category page"
* Added option "Show rewards points menu on frontend"
* Fixed share messages

---

### 1.1.23
*(2017-02-10)*

#### Features
* Added Facebook share button
* Added option to hide rewards menu from frontend
* Added new condition "Is Rewards Points used" to earning cart rule
* Added abitily to import/export rewards transactions
* Added options to configure include/exclude tax for Earning and Spending rules
* Added ability to add notification to product earning rule

#### Fixed
* Socail urls for multistores
* Category condition's operator "is" for product rule
* Replaced old Facebook tag with valid html5 tag
* SmartWave Porto theme compatibility
* An issue when "Product's earning points do not display on Home page "
* UTF characters in "Point Unit Name"
* "Customer is not active for long time" condition
* Minicart's total and item number do not update correctly
* Actions for cart Earning Rules
* An issue when "Product points as currency equivalent" displays in checkout 
* Points for Payment rule method
* Compatibility with theme Infortis Ultimo
* Included Fixed Product Tax in rewards spending points
* Compatibility with MageCheckout Onestepcheckout v2

---

### 1.1.22
*(2016-12-05)*

#### Fixed
* Cart rule condition "Payment method"
* Wrong calculation for Spending rules' "Spend minimum"

---

### 1.1.21
*(2016-11-29)*

#### Fixed
* Displaying of Rewards points for grouped and bundle products

#### Features
* Added earning behavior rule event "Customer places an order"
* Allowed display on Product pages points as money equivalent

---

### 1.1.20
*(2016-11-18)*

#### Fixed
* Calculation of Spending rules

---

### 1.1.19
*(2016-11-08)*

#### Fixed
* Tables formatting for small screen or cell phone 

---

### 1.1.18
*(2016-11-08)*

#### Features
* Added Rewards block to admin create order form

#### Fixed
* minor bugs

---

### 1.1.17
*(2016-11-04)*

#### Fixed
* Minicart total and item number do not update correctly
* Social js are not loading deferred

---

### 1.1.16
*(2016-10-24)*

#### Features
* Added ability to show AddThis buttons in Rewards social block

#### Fixed
* trigger_recollect caused infinite loop

---

### 1.1.15
*(2016-10-13)*

#### Fixed
* Compatibility with Bss/DeferJS
* Compatibility with onestepcheckout
* Earning rule conditions do not display all attributes
* Magento page cache js error
* Conflict with Amasty Rules
* Undefined variable in social buttons block
* Integration with MageCheckout Onestepcheckout v2

#### Features
* Added Rewards block to checkout page
* Added integration with AddThis
* Added ability to create custom behavior rules


---

### 1.1.14
*(2016-09-09)*

#### Fixed
* Conflict with Amasty Rules

---

### 1.1.13
*(2016-09-07)*

#### Fixed
* Conflict with Amasty Rules
* Issue when js error appears, when social buttons are disabled

---

### 1.1.12
*(2016-09-05)*

#### Fixed
* Issue when cart rules with "Fixed Amount Discount for Whole Cart" show error in cart

---

### 1.1.11
*(2016-08-31)*

#### Improvements
* Compatibility with 2.1.1

---

### 1.1.10
*(2016-08-05)*

#### Features
* Added settings "Show number of points on the product page"

---

### 1.1.9
*(2016-08-03)*

#### Fixed
* Issue when reward points are not being deducted after using them through Paypal
* Issue when earning rule not working for referral

#### Features
* Added integration with MageCheckout Onestepcheckout

---

### 1.1.8
*(2016-07-06)*

#### Features
* Added integration with MageStore Onestepcheckout

---

### 1.1.7
*(2016-07-06)*

#### Improvements
* Added new conditions "Is referral"
* Added variables for Notification Rule message

---

### 1.1.6
*(2016-06-24)*

#### Improvements
* Support of Magento 2.1.0

---

### 1.1.5
*(2016-06-21)*

#### Fixed
* Issue during discount coupon codes apply

---

### 1.1.4
*(2016-06-13)*

#### Features
* Show message about product rule points for grouped products

---

### 1.1.3
*(2016-06-08)*

#### Fixed
* Incorrect points calculation when we remove items from the cart
* Incorrect URL in spending rules grid (effects from 1.0.13)

---

### 1.1.2
*(2016-06-02)*

#### Fixed
* Customer sign up earning rule does not allow save customer in backend
* If a rule is disabled in Earning Rules/Spending Rules, you have no way to find it back
* Error when we give points for newsletter subscription

---

### 1.1.1
*(2016-06-01)*

#### Fixed
* Styles for reward points on home page

---

### 1.1.0
*(2016-05-31)*

#### Improvements
* Show the amount of points able to earn in the product listing page

---

### 1.0.13
*(2016-05-30)*

#### Improvements
* Ability to filter grids by website

---

### 1.0.12
*(2016-05-27)*

#### Improvements
* Added website column to spending/earning/notification grid

---

### 1.0.11
*(2016-05-17)*

#### Improvements
* Improved twitter script loading

---

### 1.0.10
*(2016-05-10)*

#### Fixed
* Fixed formatting of referral invitation

---

### 1.0.9
*(2016-05-06)*

#### Improvements
* Improved notification rules messages for different currencies

#### Fixed
* Fixed "Pin It" button

---

### 1.0.8
*(2016-04-26)*

#### Fixed
* Fixed rules conditions issus
* Fixed currency conversion

---

### 1.0.7
*(2016-04-11)*

#### Improvements
* Show current rewards points in my account dropdown

#### Fixed
* Issue with menu
* Fixed issue with saving of rules if magento use custom locale

---

### 1.0.6
*(2016-04-04)*

#### Improvements
* Improved referral link

#### Fixed
* Fixed issue with rewards for tweets.

---

### 1.0.5
*(2016-03-28)*

#### Improvements
* Styles at shopping cart page

---

### 1.0.4
*(2016-03-24)*

#### Fixed
* Issue for not working condition "total items quantity"
* Add protection from devision on zero error
* JS bug on in the social buttons

---

### 1.0.3
*(2016-03-21)* 

#### Fixed
* When admin creates a new transaction in backend, he must be required to select a customer
* Dont show message when 0 points are earned
* Issue with saving of Conditions in Spending Rules

### 1.0.2
*(2016-03-14)*

#### Fixed
* Solved issues with notification emails

---

### 1.0.1
*(2016-03-01)*

#### Fixed
* Issues with PHP 7

---
