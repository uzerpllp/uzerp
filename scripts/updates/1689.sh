#!/bin/bash
#
# $Revision: 1.1 $
#
# Remove Attachments Template Folders/Files
# Templates for attachments now use generic templates in modules/public_pages/shared/templates
#

rm -r modules/public_pages/calendar/templates/calendareventattachments
rm -r modules/public_pages/contacts/templates/companyattachments
rm -r modules/public_pages/contacts/templates/partyattachments
rm -r modules/public_pages/contacts/templates/personattachments
rm -r modules/public_pages/crm/templates/activityattachments
rm -r modules/public_pages/crm/templates/opportunityattachments
rm -r modules/public_pages/projects/templates/projectattachments
rm -r modules/public_pages/projects/templates/taskattachments

rm modules/public_pages/dashboard/templates/mydata/list.tpl
