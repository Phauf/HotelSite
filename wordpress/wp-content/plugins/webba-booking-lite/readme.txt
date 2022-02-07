=== Webba Booking ===
Contributors: Webba agency, freemius
Donate link: https://webba-booking.com/
Tags: appointment, booking, calendar, reservation
Requires at least: 5.6
Tested up to: 5.8.1
Stable tag: 4.1.6
Requires PHP: 5.6
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Webba Booking is a powerful and easy to use WordPress appointment and reservation plugin. Fully responsive, multi-service and flexible.
== Description ==

Webba Booking is a powerful and easy to use [WordPress booking plugin](https://webba-booking.com/) especially thought for Service Providers to optimize their time and for the comfort of their customers.

[Demo](https://webba-booking.com/demo/)
[Documentation](https://webba-booking.com/documentation/)

== Areas of usage ==

* **Specialist booking** (personal trainer booking, consultant booking, lawyer booking, wedding coordinators, contractor booking, therapist booking, tutor booking, photographer booking, dance instructors booking, coach booking, any other specialist who needs appointment booking)
* **Wellness and beauty booking** (beauty salon booking, nail salon booking, hair salon booking, cosmetologist booking, hairdresser booking, barbershop booking, manicurist booking, SPA booking, swimming pool booking, massage booking)
* **Activities booking** (event booking, ticket booking, tour scheduling, golf booking, games booking, quest rooms booking, escape room booking, gym booking, fitness booking)
* **Medical booking system** (doctor booking, dentist booking, clinic booking system and other ares related to medicine)
* **Restaurant booking** (table reservation, dining booking, private room reservation)
* **Equipment and transport rental** (car rental system, bike rental, boat rental, yacht booking, technics booking, any other kind of hourly rental)
* **Education and lessons booking** (language school booking system, facility booking, language lessons booking, private tutor booking, driving school lessons booking, private teachers booking, school booking, classroom booking, seminar booking)
* **Service booking** (car service booking, cleaning booking, repairment booking)

== Features ==

= Booking (front-end) =

* 80+ options for appearance customization
* 79 design presets
* Editable front-end texts
* Fully responsive plugin
* Fully translatable and multilingual (WPML compatibility) plugin
* Unlimited custom fields with CF7 integration
* Basic mode for fast and simple booking process
* Extended mode for advanced time search
* Editable date and time format
* Users local time in time slots
* Form for one or multiple services
* Group services by categories
* One or multiple bookings per time slot
* Hidden or visible (with option to add users data) booked time slots
* Mask input control for phone numbers
* Option to hide fom after booking
* Option to do a unique booking form for each Service
* Redirection after booking with javascript API
* Different time slot formats for short or extended presentation
* Single or multiple bookings in one session
* Option to limit appointments count in multiple mode
* Option to skip time selection (default time)
* Option to skip date selection (default date)
* Option to control count of applicants required for booking
* Popup or dropdown date inputs
* Cancellation of appointment by user without registration
* Buffer option to prevent cancellation just before the appointment time

= Administration =

* Manual appointments in the backend
* Shortcode builder
* Responsive appointments table
* Booking form appearance preview
* Group services by categories
* CSV export
* Options for sharing access to Services with other WP users
* Options for backend customization
* Easy translation from backend
* Approval or cancellation of appointments with the link sent in the notification (without logging to dashboard)

= E-mail notifications and reminders =

* Email notification to the user on booking
* Email notification to the administrator on booking
* Email notification to the user when the administrator approves the appointment
* Email notification to the administrator when the customer cancels the appointment
* Email notification to the user when administrator cancels the appointment
* Email notification to other users added in the booking form (invitation)
* Appointment reminder (date to be defined)
* Administrator’s agenda for the next day
* Invoice (sent to user on booking or on approval)
* Any notification on booking, invoice and reminder sent to a user can be unique for each Service
* All messages and subjects fully editable
* Opportunity to add images in the notifications
* Full compatibility with the popular SMTP plugins


== Pro version features ==

* Payments with PayPal
* Payments with Stripe
* Coupons
* Export to CSV
* Integration with WooCommerce
* 2-ways synchronization wit the Google calendar
* iCal attachments
* SMS notifications and reminders



**Minimum Requirements**
WordPress 3.9 or greater
PHP version 5.6 or greater
MySQL version 5.0 or greater

== Installation ==

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser.

To do an automatic install of Webba Booking, log in to your WordPress dashboard, navigate to the Plugins page and click Add New.

In the search field type "Webba Booking" and click Search Plugins. Once you have found our plugin you can install it by simply clicking Install Now.

After clicking that link you will be asked if you are sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

**Manual installation**
The manual installation method involves downloading our plugin and uploading it to your web server via your favorite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installations wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.


**Getting started**
When the title of the plugin has appeared on your dashboard, you may start the setup. The following steps are required to make it possible for customers use your newly installed plugin:

Browse all the tab and fill with your datas and desired settings
Add a booking form to a website page using one of the [available shortcodes](https://webba-booking.com/documentation/shortcodes/examples-of-manual-shortcode-usage/).



**Upgrade to pro version**

1. Deactivate free version.
2. Install and activate premium version.

== Screenshots ==

1. screen_01.png

3. screen_02.png

4. screen_03.png

5. screen_04.png

6. screen_05.png

7. screen_06.png

8. screen_07.png

9. screen_08.png

10. screen_19.png

11. screen_10.png

12. screen_11.png

== Frequently Asked Questions ==

**How can I embed the booking form into a post/page?**
Just find the button Webba Booking form above content editor for a post or a page.

== Upgrade Notice ==

= 4.1.6 =
New features, multi-site support, vulnerability fix

== Changelog ==

13/10/2021

4.1.6

* Improvements: option to show a message if date dropdown is empty
* Improvements: doc and docx formats available for upload
* Improvements: XSS vulnerability fixed
* Improvements: added multi-select input support in custom forms
* Improvements: multi-site support
* Bugfix: incorrect usage limit in coupons on certain configurations
* Bugfix: bug with the cancellation of the booking on the Appointments page

25/09/2021

4.1.4

* Bugfix: bug with automatic redirection when payment is completed using coupon_name
* Bugfix: bug with the service placeholder in the payment item
* Bugfix: DST issues

22/09/2021

4.1.3

* Bugfix: minor bugfixes

24/08/2021

4.1.1

* Bugfix: minor bugfixes

20/08/2021

4.1.1

* Improvements: option to define status of the booking after payment with stripe
* Improvements: option to not tax the deposit amount
* Improvements: WooCommerce coupon restrictions for Service and Pricing rules
* Bugfix: minor bugfixes

27/07/2021

4.1.0

* Improvements: usage of table prefix in the plugin table names
* Improvements: sending SMS on manual booking
* Improvements: the ability to set multiple ICS calendars
* Bugfix: issue on the schedule page with past bookings
* Bugfix: issue with notifications when multiple bookings added manually

06/07/2021

4.0.74

* Bugfix: bug with the credit card payment initialization

05/07/2021

4.0.73

* Improvements: show days in one row if there is only one timeslot available
* Improvements: connection with external ics calendars (integration with Outlook)
* Improvements: expanded list of possible statuses
* Improvements: added a confirmation button when deleting bookings on the Schedule page
* Improvements: the ability to send reminders by email on the current day
* Improvements: sending data to the administrator if it is impossible to connect to the Google calendar
* Improvements: show service description for single service forms
* Improvements: service fees (deposits)
* Improvements: option to take into account the bookings added by Webba during the two-way synchronization
* Improvements: limiting options improved
* Improvements: option to load plgin javascript in footer
* Bugfix: issue with long service descriptions
* Bugfix: past bookings are not displayed on the Schedule page
* Bugfix: bug with night hours
* Bugfix: bookings are not added to Google calendar when paying with WooCommerce
* Bugfix: error on PHP 8.0 on certain configurations

17/06/2021

4.0.72

* Bugfix: minor bug fix

16/06/2021

4.0.71

* Bugfix: bug with limit options

01/06/2021

4.0.69

* Bugfix: bug with automatic cancellation of bookings on certain configurations

18/05/2021

4.0.68

* Bugfix: minor bug fix

17/05/2021

4.0.67

* Improvements: option to connect pricing rule with the number of places booked
* Improvements: option to allow time slots cross midnight
* Improvements: option to set general service fee
* Improvements: compatibility with the Food Book plugin
* Bugfix: bug with pricing rules on certain configurations
* Bugfix: bug with reminders on certain configurations
* Bugfix: bug with unexpected warning messages in the dashboard
* Bugfix: bug with script caching on certain configurations

14/04/2021

4.0.65

* Improvement: new option in pricing rules
* Improvement: option to set service fee
* Improvement: additional check of reminders data
* Improvement: dynamic total amount in the form label
* Improvement: easy duplication of data in the dashboard
* Improvement: automatic cancellation does not affect free services
* Improvement: service description placeholder #description for the form label
* Bugfix: bug with GDPR option and ongoing bookings
* Bugfix: bug with availability in overlapping intervals
* Bugfix: bug with inactive timeslots
* Bugfix: bug with the option 'Skip time slot selection'

12/03/2021

4.0.57

* Bugfix: bug in multi-service booking on certain configurations

12/03/2021

4.0.56

* Bugfix: bug with the local time conversion on certain time zones

09/03/2021

4.0.55

* Bugfix: bug with availability in the Calendar on iOS device

25/01/2021

4.0.46

* Bugfix: custom field values are not exported to CSV
* Bugfix: bug with the booking cancellation

22/01/2021

4.0.45

* Improvement: the ability to use HTML in the 'Booking form label' option
* Improvement: option to disable considering the availability of overlapping time intervals
* Improvement: option to remove not processed placeholders
* Bugfix: attachment in the notification sent to a customer
* Bugfix: bug with popup calendar on certain configurations
* Bugfix: bug with the email sent to a customer form the group
* Bugfix: IDs in the CSV export

08/01/2021

4.0.44

* Improvement: 2-way synchronization on checking availability in the calendar
* Improvement: option to disable shortcode buttons in the editor

05/01/2021

4.0.42

* Bugfix: bug with reminders

30/12/2020

4.0.41

* Improvements: tomorrow's agenda can be customized using the appointment loop
* Improvements: a new type of email notifications - when booking changed
* Bugfix: bug with the option 'Synchronization of group services'

23/12/2020

4.0.38

* Improvements: SMS notifications

12/22/2020

4.0.36

* Bugfix: wrong time in the 'on booking' notification on certain configurations

12/18/2020

4.0.35

* Bugfix: minor bug fixes
* Bugfix: database error on certain configurations

12/17/2020

4.0.33

* Improvements: pricing rules
* Bugfix: bug with the time format on the Appointments page
* Bugfix: bug with the service names and description translation using WPML
* Bugfix: bug with the calendar on certain iOS devices

12/09/2020

4.0.32

* Bugfix: bug with the activation on certain configurations
* Bugfix: bug with services and coupons update on WP 5.6

12/08/2020

4.0.31

* Bugfix: error on the Coupons page on certain configurations

12/02/2020

4.0.29

* Bugfix: warning message after booking added or updated on the Appointments page

11/30/2020

4.0.28

* Bugfix: bug with the ordering bookings by date or time on the Appointments page
* Bugfix: bug with the highlighting of acceptance field

11/26/2020

4.0.27

* Improvement: the number of rows in the dashboard tables is remembered
* Improvement: option to hide postal code in the Stripe element
* Bugfix: bug with the custom fields title on the Schedule page

11/25/2020

4.0.26

* Bugfix: bug in the dashboard when adding / updating elements

11/25/2020

4.0.25

* Bugfix: bug with cancelled booking in the WooCommerce cart
* Bugfix: bug with booking udpdate on certain configurations

11/24/2020

4.0.23

* Bugfix: bug with automatic cancellation of not paid booking

11/20/2020

4.0.22

* Bugfix: bug in the multi-service booking mode on certain configuration

11/18/2020

4.0.21

* Bugfix: bug with the service availability limits in multi-service booking mode
* Bugfix: minor bugs fixed

11/16/2020

4.0.19

* Improvement: break added to default business hours
* Improvement: ability to set the default number of dates displayed on the Appointments page
* Improvement: switching the time format when editing services
* Bugfix: unused requests removed on the dashboard pages
* Bugfix: removed tags in service description
* Bugfix: media button in service description not appeared
* Bugfix: translation issues
* Bugfix: bug with the name of coupon on the Appointments page
* Bugfix: cancel button removed on the multiple booking tool
* Bugfix: issue with the custom fields on the Schedule page

11/03/2020

4.0.17

* Bugfix: problem when exporting a large number of bookings
* Bugfix: bug with the service names on the Schedule page on certain configurations
* Bugfix: bug with service edit on certain configurations
* Bugfix: bug with the Suitable hours form

10/31/2020

4.0.15

* Improvement: up to 5000 rows in the backend
* Bugfix: bugs related to translation
* Bugfix: bug with availability calculation on certain configurations

10/25/2020

4.0.12

* Bugfix: bug with the availability calculation when booking is added manually
* Bugfix: bug on the Schedule page

10/24/2020

4.0.9

* Bugfix: minor bugfix related to data conversions

10/23/2020

4.0.8

* Bugfix: bug with On approval notifications

10/22/2020

4.0.7

* Improvement: maximum rows per page increased
* Improvement: using HTML tags in the payment methods messages
* Bugfix: bug with adding bookings manually on certain configurations

10/21/2020

4.0.6

* Bugfix: bug with the chosen control

10/20/2020

4.0.5

* Improvement: complete backend update

10/12/2020

3.8.64

* Bugfix: bug with the Google calendar integration on certain configuration

10/06/2020

3.8.63

* Bugfix: bug with automatic deletion of attachments

10/03/2020

3.8.62

* Bugfix: bug with the custom fields on Appointments page

10/02/2020

3.8.61

* Improvement: default date range increased on the Appointments page
* Improvement: translation for the service name placeholder
* Improvement: notification to admin if booking added manually
* Improvement: store IP if booking added manually
* Bugfix: bug with the custom column titles if booking added manually

08/27/2020

3.8.55

* Bugfix: issues with the cached data in the service settings popup

08/20/2020

3.8.54

* Bugfix: bug with custom fields when booking manually

08/16/2020

3.8.53

* Bugfix: bug with placeholders.

08/12/2020

3.8.52

* Improvement: storing cancellation time
* Improvement: storing creation time
* Bugfix: date conversion issue in certain timezones
* Bugfix: bug with the option 'Set status after booking is paid with WooCommerce to'

08/12/2020

3.8.51

* Improvement: translation of service names with Polylang
* Improvement: option to choose the version of Stripe API
* Improvement: compatibility improvements for the WordPress 5.5
* Bugfix: #appprice placeholder now takes into account the number of booked places
* Bugfix: compatibility issues with themes that use jQuery Chosen plugin
* Bugfix: loading Stripe javascript removed from the free version
* Bugfix: empty custom fields if title of column is set
* Bugfix: issues with integration with the Google calendar on certain configurations

07/15/2020

3.8.47

* Bugfix: bug with the 'tank you' message on certain configuration.


07/15/2020

3.8.46

* Improvement: preparation time (limit) makes no effect on manual booking.
* Bugfix: bug with the 'tank you' message when 'add to cart' option is enabled

07/13/2020

* Bugfix: issues with the booking status when WooCommerce is used for payments  

08/07/2020

3.8.44

* Improvement: options to send separate notification when the booking is added manually
* Improvement: option to set titles for the custom fields ids in the backend interface
* Improvement: option to show the 'created on' date in the Appointment table
* Bugfix: bug with additional fields used in Stripe
* Bugfix: bug with checkboxes in multi-service booking
* Dev: 'timeslot rendered' event added in js code (wbk_timeslots_rendered)

07/21/2020

3.8.42

* Improvement: reply-to headers for the notifications
* Improvement: storing of the IP address of the admin who cancels the booking
* Improvement: options to translate cancellation and approval messages for admin
* Improvement: support of translation of CF7 forms with the Polylang
* Bugfix: bug with the time format on the Schedule page
* Bugfix: bug with the preparation time
* Bugfix: bug with the sending copy of service emails
* Bugfix: iСal export fixed
* Bugfix: bug with availability calculation on certain configuration
* Dev: filter for the form rendering
* Dev: filter for the options used in Google calendar integration

06/10/2020

3.8.37

* Bugfix: bug in CF7 integration

06/09/2020

3.8.36

* Bugfix: compatibility issues

06/05/2020

3.8.44

* Improvement: option to show category dropdown for multi-service booking
* Improvement: option to send alert if Google calendar has been unauthorized
* Improvement: option to disable Stripe Api
* Improvement: option to load Stripe JS only on a page with booking forms
* Improvement: option allow html in select date label options
* Bugfix: issue in the mass operation on dates with DST event

05/26/2020

3.8.34

* Bugfix: issue with time zones.

05/25/2020

3.8.33

* Bugfix: compatibility issue

05/18/2020

3.8.32

* Improvement: category attribute for the multple service shortcode
* Improvement: preset appointment status on creation depending on default status
* Bugfix: date formatting error for dates when DST event occurs
* Dev: filter for the number of places per time slot

04/22/2020

3.8.31

* Bugfix: bug with the DST on certain configurations


03/26/2020

3.8.44

* Improvement: availability to use custom names for the name, email and phone field in CF7 forms
* Bugfix: wrong end time when using local time

03/07/2020

3.8.28

* Bugfix: bug in WooCommerce integration.

03/05/2020

3.8.27

* Improvement: Stripe API updated
* Improvement: Reply-To header in the notification to admin is set with the customer's email_label
* Improvement: option to set special hours for all services
* Improvement: cancellation buffer can be set in minutes
* Improvement: hooks for setting the 'Skip time slot' option per services
* Improvement: new function to add custom JavaScript for the payment buttons click events
* Improvement: option to show the local time in Start-End format
* Improvement: Start-End format of the time on the Appointments page
* Bugfix: bug with availability in the calendar when using the option 'Disable time slots after X hours'
* Bugfix: bug with the activation on certain websites
* Bugfix: minor conflicts resolution
* Bugfix: wrong time after saving appointment on the Appointments page

3.8.24

01/24/2020

* Bugfix: issues with coupons.

3.8.23

01/23/2020

* Improvement: the ability to give access to appointments to users with the subscriber role

3.8.22

01/22/2020

* Improvement: option to set number of days to show in extended mode
* Improvement: special hours option improved to set rules for all services
* Improvement: new placeholder for service description - #service_description

* Improvement: images allowed in service description
* Bugfix: cancellation issues
* Bugfix: cancel button not worked on certain configurations

* Bugfix: bug with the auto-scroll in multi-service booking mode
* Bugfix: bug with the shortcode form

3.8.21

01/09/2020

* Improvement: support of the character Count in the CF7 forms
* Improvement: booking is marked as paid when applied coupon with the fixed discount more than total amount
* Improvement: new placeholders for date and time when booking is made #booked_on_date, #booked_on_time

3.8.19

01/06/2020

* Bugfix: bug with the multi-service booking.

3.8.18

01/05/2020

* Bugfix: minor Bugfix.

3.8.17

01/03/2020

* Improvement: option to override the business hours of certain services on specific dates
* Improvement: option to skip date selection in multi-service booking mode
* Bugfix: bug with the broken loading image on certain themes
* Bugfix: bug with the translation of coupon input placeholder
* Bugfix: scroll issue when date and service fields are hidden
* Bugfix: bug with the shortcode generation form

3.8.14

12/16/2019

* Bugfix: bug with the CSV export

3.8.12

12/13/2019

* Bugfix: issues with datepicker

3.8.11

12/12/2019

* Bugfix: compatibility issues

3.8.9

12/12/2019

* Bugfix: but with the update of the past bookings

3.8.8

11/27/2019

* Improvements: new placeholder #uniqueid to insret random tokens
* Improvements: option to limit the number of bookings per email per day
* Bugfix: bug with the locking time before and after booking

3.8.6

11/19/2019

* Bugfix: minor Bugfix

3.8.5

11/14/2019

* Bugfix: bug with the date translation

3.8.4

11/14/2019

* Bugfix: critical Bugfix realted to the time zone conversion

3.8.2

10/27/2019

* Bugfix: bug with activation on certain environment

3.4.24

05/04/2019

* Bugfix: bug with custom fields

3.4.23

05/03/2019

* Improvement: new mass operation tool for adding multiple booking from backend
* Improvement: ability to set custom fields when creating new booking on Appointments page
* Improvement: option to lock time slot on Schedule page if  places already booked
* Improvement: schedule tools (mass operations) improved
* Improvement: option to set the method of availability calculation
* Improvement: option to set ascending and descending service order
* Improvement: preparation time in minutes
* Bugfix: bug with current category placeholder
* Bugfix: bug in interface of Appointment popup

3.4.7

02/04/2019

* Improvements: option to set scroll offset value.
* Improvements: option to set format of delimiter in the price.
* Improvements: range selection mode
* Improvements: new placeholders for range selection mode
* Bugfix: wrong date above the time slots in multi-service mode.
* Bugfix: bug with automatic cancellation of appointments added manually.

12/16/2018

* Improvements: line removed in multiservice booking when services are not shown.
* Improvements: specific error message if time slot already booked instead of generic message.
* Improvements: ready for integration with International phone input plugin.
* Improvements: option to make holiday option recurring.
* Improvements: option to show local time in a day label above the time slots.
* Improvements: new placeholders for range fields #timerange and #timedaterange.
* Bugfix: symbol “S” in date picker.
* Bugfix: conflict with the Polylang plugin.
* Bugfix: bug with the custom fields in the Schedule page.
* Bugfix: bug with usernames in the Services page.
* Bugfix: bug with updating appointment on a closed date.

3.4.0

10/26/2018

* Improvements: option for adding gaps before and after appointments improved
* Improvements: approval of multiple appointments with one email link
* Improvements: multiple emails in "Send copies of service emails to" option (comma*separated)
* Improvements: titles of custom field columns in the Appointments page
* Improvements: new placeholder for appointment duration (#duration)
* Improvements: option for automatic selection of all services when the Appointments page loaded
* Improvements: option to lock time slots before and after appointment(s)
* Improvements: services table UI improved (issue with long descriptions fixed)
* Bugfix: bug with date format in popup calendar
* Bugfix: bug with service descriptions
* Bugfix: bug with not required field in booking form
* Bugfix: appearance of popup calendar on iPhones fixed
* Bugfix: bug with date format after update from lite to premium

3.3.96

09/19/2018

* Bugfix: bug with classic calendar in Lite version

3.3.94

09/17/2018

* Improvements: classic date picker
* Improvements: option "Lock time slot if at least one place is booked" use Autolock mode
* Improvements: option to lock all day if it list one time slot is booked
* Bugfix: bug with adding events to Google calendar when appointment approved by the link
* Bugfix: bug with the number of available places when edit appointments in the Appointments page
* Bugfix: bug with night hours
* Bugfix: bug with the last day when date range and date dropdown used

3.3.91

08/25/2018

* Bugfix: checkbox not working when time is slot selection skipped
* Bugfix: bug with empty categories
* Bugfix: availability issues
* Bugfix: phone number issues after previouse update
* Bugfix: bug with the minimum places per time slot option of the service

3.3.86

08/10/2018

* Bugfix: bug with availability in multi-service mode

3.3.85

08/09/2018

* Improvements: compatibility with php 7.2
* Improvements: new placeholder #status
* Improvements: option to show / hide columns on Appointments page
* Improvements: layout of tables in dashboard improved

3.3.84

08/04/2018

* Improvements: options to add text before and after time in time slots
* Improvements: subtotal and tax placeholders formatted according to price format
* Improvements: new placeholder #one_slot_price
* Bugfix: bug with placeholders used for user name in the dashboard
* Bugfix: bug with availability of manually unlocked days in the popup calendar

3.3.82

07/24/2018

* Improvements: using of the first booking for the placeholders out of loop
* Improvements: message to administrator when administrator cancel the appointment by link
* Improvements: custom fields for the customer name in the Schedule page
* Improvements: option to redirect when payment with Stripe is succeed
* Improvements: new placeholders #subtotal_amount, #tax_amount
* Bugfix: bug with acceptance field

3.3.77

07/14/2018

* Improvements: coupon usage number in the Coupons page
* Improvements: coupon column in th Appointments table
* Improvements: option to use additional fields in Stripe checkout
* Bugfix: bug with availability in popup calendar
* Bugfix: Bugfixed amount coupons when price is zero
* Bugfix: bug with 24-hours time slots and night hours mode

3.3.73

07/03/2018

* Improvements: continuous appointments
* Improvements: option to skip “search time slots” button in extended mode
* Improvements: added hook for external validation of booking form fields
* Improvements: availability check-in in the popup calendar improved (option to set number of days to check)
* Improvements: preparation time in hours

3.3.68

06/24/2018

* Improvements: details of previous appointments in the time slot
* Improvements: option to skip time slot selection in Extended mode
* Improvements: option to set minimal places per time slot
* Improvements: option to set scrolling container
* Bugfix: bug with night hours
* Bugfix: bug with DST in Schedule tools

3.3.63

06/11/2018

* Improvements: cancelled appointments in the dedicated table
* Improvements: option to define the count of days in extended mode
* Improvements: option to lock time slot if at least one place is booked
* Improvements: more placeholders for booked time slot
* Improvements: placeholder for the low limit in the checkout button
* Bugfix: interface of the Schedule page (layout issues)
* Bugfix: option to re-configure the booking form label in multi-service booking
* Bugfix: issues with not displayed appointments on the Schedule page (after count of places changed in the service settings)

3.3.52

04/24/2018

* Bugfix: bug with custom fields placeholder
* Demo: WooCommerce integration

3.3.42

04/02/2018

* Improvements: custom fields interface improved
* Improvements: amount field on Appointment page
* Improvements: WPML integration improved (store data language for reminders)
* Improvements: service order (priority option)
* Improvements: option to hide service checkboxes in multi-service mode
* Improvements: EU GDPR Compliance
* Improvements: service id in the Services table
* Improvements: Multi-service booking
* Improvements: Option for mass cancellation of appointments with the link (for administrator)
* Improvements: Option to not allow more than one appointment per time slot for the user (restriction by email)
* Improvements: Option to not allow more than one appointment for the user on given service (restriction by email)
* Improvements: Option to automatically delete appointments with the Awaiting approval status
* Bugfix: bug with extended mode and time zones with DST
* Bug with the time zone when appointment approved
* Bugfix: DST issues fixed
* Bugfix: bug with the day availability after 360 days from current date
* Bugfix: bug in the Schedule page in IE 11

3.3.25

03/03/2018

* Bugfix: bug with the local time in extended mode

3.3.23

02/21/2018

* Improvements: gateway for using Conditional Fields for Contact Form 7
* Improvements: "night hours" option to include time slots in a previous day
* Improvements: option to show only local time in time slots
* Improvements: #local placeholder to show local time in the booking form label
* Bugfix: bug with notification duplicate on certain configurations
* Bugfix: bug with Safari and category list shortcode
* Demo: iCalendar integration

3.3.18

02/10/2018

* Improvements: #id placeholder in the Appointment information option (used in payment and cancellation pages)
* Improvements: option to lock / unlock time for category on given date and time ranges
* Improvements: option to show appointments locked by autolock feature as "booked" in front-end
* Improvements: option to show end time for time slots on Schedule page
* Improvements: option to show end time in the interface of Appointments page
* Bugfix: bug with #selected_count pleaceholder in the Booking done messages and form label
* Bugfix: bug with the long custom forms
* Bugfix: Bugfixes with the autlock on certain configurations
* Bugfix: bug with step option of the service on certain configurations

3.3.15

01/30/2018

* Improvements: placeholder in subjects when multiple booking enabled.
* Improvements: 15 new subject placeholders.
* Improvements: #selected_count placeholder for subject and messages to show count of booked tims slots in multiple mode.
* Improvements: option to setup the format of the decimal part of the price.
* Improvements: #start_end placeholder for the "Appointment information" option.
* Improvements: minimal limit for multi-booking.
* Improvements: offline payment methods block automatic cancellation of appointments.
* Improvements: exclusive checkboxes (CF7 forms).
* Improvements: service category list on frontend.
* Improvements: placeholder for time range in the booking form label option
* Improvements: appointments limit for services (override the global limit setting)
* Improvements: action to hook booking event in external plugins
* Improvements: date range placeholder to set start-end format of appointment time
* Improvements: option to hide Suitable houts in the extended mode
* Improvements: offline payments methods for services (Pay on arrival, Bank transfer)
* Bugfix: minor bugs with translation
* Bugfix: bug with the certain placeholders of the "booking done" message.
* Bugfix: bug with the certain past dates when dropdown date is enabled and service has limits.
* Bugfix: bug with the page loading time on certain configurations
* Bugfix: bug with the colons in a custom fields
* Bugfix: bug with the placeholders in the Thank you message
* Demo: prevent automatic cancellation of appointments if the offline payment method is chosen.
* Demo: payment method column in the Appointments table.
* Demo: new mode of the Google Calendar integration - import only.
* Demo: cache for 2-way Google Calendar synchronization.
* Demo: option to set redirect page for successful PayPal payments
* Demo: option to sent copies of invoices to administrator.
* Demo: two-ways Google Calendar synchronization
* Demo: option for auto-redirect to PayPal without approving
* Demo: payment link for multiple appointments
* Demo: cancellation link for multiple appointments
* Demo: Сoupons
* Demo: email notification on payment to a customer
* Demo: email notification on payment to an administrator
* Demo: option to send one notification to administrator in multiple mode
* Demo: offline payment methods block automatic cancellation of appointments.

3.3.2

12/03/2017

* Improvements: HTML in the service description
* Bugfix: bug with attachments in admin notification
* Demo: online payments with Stripe

3.2.23

11/13/2017

* Improvements: attachments in custom forms.
* Improvements: option to set checkbox field as required
* Improvements: option to set selected service in url
* Improvements: appearance options interface improved

3.2.18

11/11/2017

* Bugfix: bug with design presets list.

3.2.17

10/29/2017

* Improvements: email notification placeholder for current category
* Improvements: option to show booked time slots for multi-seats services
* Improvements: service description length increased

3.2.15

10/25/2017

* Bugfix: bug with the "booking done" message editor.

3.2.14

10/24/2017

* Improvements: more translation options.
* Improvements: option to set count of dates in the dropdown date select input.
* Improvements: option to show locked time slots as booked in the frontent.
* Improvements: cancel or approve the appointment by the link sent in administrator notification.
* Improvements: option to replace popup calendar with date select dropdown.
* Improvements: new placeholder for email notifications - category names
* Improvements: option to skip date selection in the booking form (managed by service setting Availability date range)
* Bugfix: date format in the holidays setting fixed.
* Bugfix: bug with the user name placeholder in the timeslot.
* Bugfix: DST issues fixed.
* Bugfix: compatibility with PHP 5.3.
* Bugfix: issues with time hole optimization and autolock.
* Bugfix: dropdown date select become empty when cancel button clicked.
* Bugfix: notification on cancellation bugs fixed.
* Bugfix: DST issues on the backend schedule page.
* Bugfix: hide checkout button when booking form is rendered.
* Bugfix: unlock the appointments when appointment in connected service is canceled.
* Bugfix: bug with cancellation of appointments on some versions of iOS.

3.2.0

08/27/2017

* Improvements: more compatibility with WPML.
* Improvements: ability to edit comments in the Appointments page.
* Improvements: option to show service description under select service input on frontend.
* Improvements: option to control the count of places allowed for booking (used for the services with several places per time slot)
* Improvements: option to optimize schedule when using autolock and services with different durations.
* Bugfix: bug with appointment controls in appointment table.
* Bugfix: bug with customer comments appeared as custom field.
* Bugfix: bug with appearance presets loading on certain web-servers
* Bugfix: bug with customer comments appeared as custom field.
* Bugfix: bug with searching time slots on basic mode on certain configurations.
* Bugfix: time zone conversion conflict on certain configurations.
* Bugfix: bug with service update, creation on certain configurations.
* Bugfix: reminders not working correctly after 3.1.26.
* Bugfix: bug with masked input on Chrome for Android.
* Bugfix: Bugfix editor.
* Bugfix: bug with default places count.
* Bugfix: bug with booked time slots on certain configurations.
* Bugfix: bug with subject of email sent on approval.
* Bugfix: bug with appointment time in reminders.
* Demo: integration with Google Calendar.

3.1.24

07/28/2017

* Bugfix: bug with appearance settings

3.1.23

06/06/2017

* Bugfix: bug with appearance presets loading on certain web-servers

3.1.22

05/28/2017

* Improvements: e-mail notification to administrator.
* Improvements: option to add custom fields values to customer name in the schedule and appointments table.
* Improvements: service business hours interface improved.
* Improvements: option to set the date range of service availability.
* Improvements: option to skip time slot selection and use default time.
* Improvements: apply backend date format on the Schedule page.
* Improvements: appointment table interface improved (select all services, select services by category).
* Improvements: CF7 textarea field is available as custom field.
* Improvements: minor CSS improvements.
* Improvements: control appearance in Safari mobile improved.
* Bugfix: cancel button duplicate in extended mode.
* Bugfix: email validation improved (long domains support).
* Bugfix: bug in jQuery no-conflict mode
* Bugfix: bug with autolock on certain service settings.
* Bugfix: bug with searching time slots on certain configurations of extended mode.
* Bugfix: bug with css on frontend.
* Bugfix: incorrect message on deleting data in the backend tables.
* Bugfix: bug in Safari with checkout button.

3.1.10

03/27/2017

* Improvements: input fields appearance in Safari mobile improved.
* Improvements: time slots interface improved for services with flexible step.
* Improvements: ability to set the step for services with multiple places per time slot.
* Improvements: option to disable loading javascript files of pickadate popup calendar .
* Improvements: option to set the limit of the booking in the multiple booking mode.
* Improvements: new appearance option for checkout button in the multiple mode.
* Improvements: option to make the phone field not required in a booking form.
* Improvements: placeholders in the "Booking done" message.
* Improvements: options validation improved.
* Bugfix: hide checkout button on booking done.
* Bugfix: incorrect message on deleting data in the backend tables.
* Bugfix: bug in Safari with checkout button.

3.1.6

03/07/2017

* Bugfix: bug with the loader animation.

3.1.5

08/27/2017

* Improvements: autolock of group booking services improved (option to reduce count of available places).

3.1.4

02/24/2017

* Bugfix: bug with multiple booking in one session on certain configuration.

3.1.3

02/20/2017

* Improvements: multiple time slots selection in one session.
* Improvements: group services by categories (shortcode improved).
* Improvements: autolock feature improved (ability to lock appointments by service category).
* Improvements: option to show customer's local time in the time slots, based on time zone.
* Improvements: additional masked input embeded (more compatibility with mobile devices).
* Improvements: advanced appointments status system.
* Improvements: customer's comment length extended to 1024 symbols.
* Improvements: jQuery no-conflict mode on front end (as option).
* Improvements: checking current time bofore booking form submit.
* Bugfix: bug on certain date formats in the appointments table.
* Demo: CSV-export.


3.0.15

12/12/2016

* Improvements: add, edit appointments in the appointment table.
* Improvements: option to send a copy of emails to predefined email address.
* Improvements: option to disable dates in popup calendar if no time slots available.
* Improvements: option to set preparation time for the service to prevent booking on today, tomorrow etc.
* Improvements: popup calendar style improved
* Improvements: options interface improved

3.0.9

11/13/2016

* Improvements: option to check if the rendered page has shortcode.
* Improvements: time slot autolock feature.
* Improvements: option to display cancel button.
* Improvements: autolock on manual booking.
* Bugfix: bug with booked timeslot when show booked slots enabled.
* Bugfix: unable to select current day on 7 days services.
* Bugfix: bug with autolock services with custom gaps.

3.0.5

10/27/2016

* Improvements: custom fields in the appointment table.
* Improvements: status wording improved.
* Bugfix: form refreshing.
* Bugfix: email templates with images.

3.0.4

10/22/2016

* Improvements: compatibility with Contact Form 7 Dynamic Text Extension.
* Improvements: email validation improved.

3.0.3

10/16/2016

* Improvements: option to hide the form when a booking is done.
* Bugfix: update active time slot when a booking is done.
* Demo: unlimited email templates for notifications and reminders.

3.0.1

10/06/2016

* Improvements: schedule tools to lock and unlock range of dates..
* Bugfix: booking form bug on IOS 9.

3.0.0

10/01/2016

* Improvements: custom user roles for service access.
* Improvements: custom user roles for service access.
* Improvements: unavailable dates in date picker.
* Improvements: Minor CSS improvements.
* Bugfix: default book button text fixed.
* Bugfix: date picker Bugfix.
* Demo: online payments interface.

2.2.6

08/22/2016

* Improvements: appointments page.
* Improvements: connection between services.
* Improvements: frontend css improvements.
* Improvements: editable form title with placeholders.

2.2.3

08/11/2016

* Bugfix: error if appointment duration is less then 10 minutes.

2.2.2

08/10/2016

* Improvements: Javascript API (enable to trigger custom js code on booking).

2.2.1

07/28/2016

* Improvements: option to display booked time slot.
* Improvements: option to edit booked time slot text (with placeholders).
* Improvements: minor css improvements.
* Bugfix: slot with 0 available.
* Bugfix: error message in booking form with defined service.
* Bugfix: time slot interval witout gap.
* Bugfix: issue with the custom checkboxes.

2.1.7

07/11/2016

* Improvements: Saving data on uninstall.

2.1.6

07/10/2016

* Improvements: format of time on the time slot block.
* Improvements: Detailed or simple timeslot view.
* Improvements: CF7 select field validation (required / not required).

2.1.5

07/06/2016

* Bugfix: phone mask issue.
* Bugfix: list of users at service page.
* Improvements: business hours setup with 15 minutes step.

2.1.2

06/26/2016

* Minor backend improvements.

2.1.1

06/26/2016

* Appointments limit removed.
* Email notifications removed.

2.1.0

06/15/2016

* HTML available for the translation settings.
* Bugfix.


2.0.7

05/30/2016

* Frontend css improved.
* Service duration minimal limit removed.

2.0.4

05/19/2016

* Minor Bugfix.

2.0.0

05/07/2016

* Appearance customization: 79 frontend design presets, 80+ options.
* CF7 acceptance field support.
* Active time slot visualization.
* Minor Bugfix.

1.3.3

03/24/2016

* Color customization options.

1.3.1

03/01/2016

* Team booking.
* Frontend labels as editable options.
* Minor backend interface improve.
* Minor Bugfix.
* Custom fields in booking form feature available.
* Integration with the Contact Form 7.

1.1.0

02/02/2016

* Appointment duration limit removed.
* Romanian language available.
* Russian language availabale.

1.0.8

01/22/2016

* German language available.
* Minor Bugfix.

1.0.5

01/05/2016

* French language available.
* Minor Bugfix.

1.0.4

12/16/2015

* Booking form style updated
* Translation enabled (pot-file included)
* Bugfix

1.0.2

12/15/2015

* Bugfix

1.0.1

12/10/2015

* Backend interface improvements

1.0

12/07/2015

* Initial release
