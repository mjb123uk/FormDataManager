FormDataManager
===============

This Modx extra is a Custom Manager Page (CMP) that can be used to define layouts for viewing and exporting data created from forms.
Currently, this works with form data generated via the Formz extra, FormIt saved forms (using the FormItSaveForm hook) or Custom Tables.

The front page lists the forms that exist in the site. By selecting the form you can define a layout to suit your processing requirements.
An additional tab provides the ability to add tables i.e. Custom tables used to store form data.

NOTE: New from version 1.3+ an extra tab has been added so that you can create templates for the data view/export.
This is a useful facility if you wish to export multiple forms in a standard format. The option is provided when defining a layout for
a form (or table) to use a defined template, you then need to map the fields from the form/table to the predefined ones in the template.
When setting up the template you enter comma seperated column names, fields types (text/number/date), default values & an optional field
that contains a date that can be used for the selection range when exporting.
It is now also possible to mark formz/formit forms as inactive so as to reduce the number appearing in each tab.

Once the layout has been created you can view the existing records and then choose to export the data.
When defining the layout you can select which fields are to be included, change the sequence/order of the fields (N/A if template),
set alternative column titles (N/A if template) and if needed set a default value to be used if the value of a field is empty.
You can also add additional export columns with mapped fields if using a template and/or default values if needed

The export file will match the defined layout including any required default values irrespective of the form fields saved.

Optionally, you can specify a date range for the export. By default the Start Date will be set to the last exported End Date i.e. just
the latest data.

If you change the structure of a form or table you will need to remove the existing layout and then define a new form layout.
Note - please ensure you have exported any existing form data that you may need using the existing layout before removing 
and defining a new layout.

This CMP uses the Modx 2.3+ menu system and by default creates a menu entry under the Extras (Components) menu.

NOTE: Since 1.3+ the processors have been made classes and all have been moved to a 'mgr' sub-folder within the processors folder.
The table (FdmLayouts) used by this extra has been modified to include 3 additional fields required for the extra functionality.

Credit to the developers of Formz & Formit some code extracts/ideas used in this Extra.
