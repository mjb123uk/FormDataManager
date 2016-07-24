# FormDataManager


This Modx extra is a Custom Manager Page (CMP) that can be used to define layouts for viewing and exporting data created from forms.
Currently, this works with form data generated via the Formz extra, FormIt saved forms (using the FormItSaveForm hook) or Custom Tables.

The front page lists the forms that exist in the site. By selecting the form you can define a layout to suit your processing requirements.
An additional tab provides the ability to add tables i.e. Custom tables used to store form data.

Once the layout has been created you can view the existing records and then choose to export the data.
When defining the layout you can select which fields are to be included, change the sequence/order of the fields,
set alternative column titles and if needed set a default value to be used if the value of a field is empty.

The export file will match the defined layout including any required default values irrespective of the form fields saved.

Optionally, you can specify a date range for the export. By default the Start Date will be set to the last exported End Date i.e. just
the latest data.

If you change the structure of a form or table you will need to remove the existing layout and then define a new form layout.
Note - please ensure you have exported any existing form data that you may need using the existing layout before removing 
and defining a new layout.

This CMP uses the Modx 2.3+ menu system and by default creates a menu entry under the Extras (Components) menu.

Credit to the developers of Formz & Formit some code extracts/ideas used in this Extra.