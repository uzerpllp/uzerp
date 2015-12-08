# Change Log
All notable changes to this project will be documented in this file.


## [unreleased]
### Fixed
- Setting a custom theme now uses that theme instead of the default
- Sales order add-line was showing multiple prices for an item
- LDAP login fails if user or system company access disabled

### Added
- Allow Sales Invoices to be allocated to Projects and Tasks
- Works order view from Projects
- Add audit fields to projects
- Add the ability to purchase orders and invoices from a task
- New injector classes for optional LDAP authentication with Apache
- Add a notes field to sales order line input. Not printed on outputs by default
- Purchase orders can be linked to a sales order
- Purchase order delivery address can be sourced from a linked sales order, for output only
- Despatch notes can be creted for non-stock product lines
- Products can be marked as 'not despatchable', i.e. thier product lines can't be added to despatch notes
- Sales order lines can be released and added to a despatch note for the sales order sidebar
- Purchase order schedule document print

### Changed
- Simplify template for getting opportunity details from a project
- Phinx migrations moved to schema/phinx/migrations

### Removed
- Unnecessary action in the Task sidebar removed


## [1.5.2] - 2015-05-15
### Fixed
- Report module not working

### Changed
- adodb will be installed from our fork of v5.18 at https://bitbucket.org/uzerp/adodb

## [1.5.1] - 2015-04-09
### Changed
- Make changelog more useful to users - [keepachangelog.com](http://keepachangelog.com/)
- Modify Sales Invoice VAT handling for Prompt Payment Discounts to agree with new UK legislation, [HMRC Brief 49 (2014): VAT - Prompt Payment Discounts](https://www.gov.uk/government/publications/revenue-and-customs-brief-49-2014-vat-prompt-payment-discounts/revenue-and-customs-brief-49-2014-vat-prompt-payment-discounts)
  - Stage 1 - Don't adjust invoice VAT/net, store gross discount

### Added
- Add an option to sales orders to print an address label. You will need to add a permission for controller: SordersController, action: printAddressLabel and create a Report Definition called 'SOAddressLabel'.

## [1.5.0] - 2015-03-20
### Fixed
- View purchase orders/invoices while viewing a PO Product Line

### Changed
- adodb and less-php libraries must now be installed via [composer](https://getcomposer.org/) and not distributed in the *plugins* folder.

## [1.4.0] - 2015-03-10
### Fixed
- A person added without a company link will now be visible in the list of people

### Added
- Controller smarty templates can be overridden by users
- Support for logging to [Sentry](https://getsentry.com/)

### Changed
- The smarty php library must now be installed via [composer](https://getcomposer.org/) and not distributed in the *plugins* folder.

## [1.3.11] - 2015-02-17
### Fixed
- Stop Sales Invoice being created for customers on stop

### Added
- Allow empty sales orders to be cancelled

## [1.3.10] - 2015-01-20
### Fixed
- Closing final period of year fails when trying to update assets

### Added
- Make VAT amounts availabe to Sales Order Acknowledgement print

## [2014.3.9] - 2014-12-01
### Fixed
- Warehouse locations without GL Accounts should be visible
- Empty response when selecting User Space Tables from the menu
- When editing a task the project should not be changed
- Add NOT NULL constraint on project_id column in tasks table

### Added
- Make admin from email address configurable

### Changes to Projects Module
- Changes expenses to link to make task selection dependent on project selection in expenses header
- Add project tasks to purchase order header and make dependent on project. Also fix small annoyance where address heading incorrect on view.
- Add Purchase orders sidebar link on projects
-  Changes to allow projects and tasks in entered purchase invoices

## [2014.3.8] - 2014-10-09
### Fixed
- CSV outputs are empty

### Changes to Projects Module
- Changes to projects module
- Tidy up projects controller:
  - Removed code for old ezPDF 'reports' that no longer work
  - Removed deprecated actions for a project
  - Changed view sidebar actions (added go to and add projects under actions)
- Tidy up the projects entry and display screen:
  - Set project identifier to be job_no+name
  - Remove RAG status from view template (code left in controller as may be useful)
  - Remove edit template as not required
  - Update new template so can be used for edits as well
- Minor changes to template new.tpl
- Update so that a project can be marked as complete and the status is changed to 'C'. Doesn't change any other validations yet.
- Another change to the template to get the fields in the correct order
- Tidy up the Project Task sidebar so in the same basic order as Projects Remove calendar views as they don't currently work
- Change the order of Project Budget Item Type to Materials, Equipment, Resources, Other
- Small change to opportunity search
- Fixed bug re opportunity identifier field not displaying in drop down correctly
- Change to tasks template when adding from a project
- Fixed Bug to limit person dropdown when allocating task hours.
  - When entering task hours person dropdown now only shows people from the system company 
  - Note - this means that 'non employees' can still book hours IF they are in the system company as people.
- Fixed Bug to limit person dropdown when setting up resource templates
- Fixed bug on task hours total in View Task Totals
- Changes to the way project module handles resources
- Fixed bug which allowed hours with no employee also removed overtime checkbox as deprecated
- To add value to opportunities and products plus tidy up lik 'opportunity to project'
- Add database migration for project module updates
								  
## [2014.3.7] - 2014-09-15
### Fixed
- Duplicate NI number should be allowed in combination with finish date in HR - New Employee

## [2014.3.6] - 2014-09-09
### Fixed
- Regression in report definitions
- VAT totals incorrect on Sales Order Quote print
- Error adding periodic payment for Sales/Purchase Ledger Source

### Added
- Show person name on Sales Quote print

## [2014.3.5] - 2014-08-20
### Fixed
- Broken link on view purchase order supply/demand
- Parentheses arround address fields cause SQL error

### Added
- Print company bank account details on pro-forma invoice
- Show customer phone numbers on confirm sale
- Show line due dates on sales order acknowledgement
- Show sales order number on sales invoice

## [2014.3.4] - 2014-07-22
### Fixed
- Remove call by reference outside function definitions for PHP 5 compatibility

## [2014.3.3] - 2014-07-08
### Added
- Open up project job number for editing and enhance the search options

## [2014.3.2] - 2014-06-24
### Added
- Allow selection of a custom XSL report defintion for reports.

## [2014.3.1] - 2014-06-11
### Fixed
- Sales Order Acknowledgement Print, lines overwrite header
- CRM Activities view loads calendar instead of activities
- Adding a project fails due to system policy error
- OS Value on batch payments not being set to zero

### Added
- Use [composer](https://getcomposer.org/) to install PHP libs
- phinx for database migrations

## [2014.3] - 2014-05-27
### Changed
- First Git controlled release on bitbucket
- Older release notes at [uzerp.com](http://www.uzerp.com/releases)


[unreleased]: https://bitbucket.org/uzerp/uzerp/branches/compare/master%0D1.5.2
[1.5.2]: https://bitbucket.org/uzerp/uzerp/branches/compare/1.5.2%0D1.5.1
[1.5.1]: https://bitbucket.org/uzerp/uzerp/branches/compare/1.5.1%0D1.5.0
[1.5.0]: https://bitbucket.org/uzerp/uzerp/branches/compare/1.5.0%0D1.4.0
[1.4.0]: https://bitbucket.org/uzerp/uzerp/branches/compare/1.4.0%0D1.3.11
[1.3.11]: https://bitbucket.org/uzerp/uzerp/branches/compare/1.3.11%0D1.3.10
[2014.3.9]: https://bitbucket.org/uzerp/uzerp/branches/compare/1.3.10%0D2014.3.9
[2014.3.8]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.9%0D2014.3.8
[2014.3.7]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.8%0D2014.3.7
[2014.3.6]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.7%0D2014.3.6
[2014.3.5]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.6%0D2014.3.5
[2014.3.4]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.5%0D2014.3.4
[2014.3.3]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.4%0D2014.3.3
[2014.3.2]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.3%0D2014.3.2
[2014.3.1]: https://bitbucket.org/uzerp/uzerp/branches/compare/2014.3.2%0D2014.3.1
[2014.3]: http://www.uzerp.com/releases/uzerp-2014-3/

