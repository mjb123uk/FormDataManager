# FormDataManager


This Modx extra is a Custom Manager Page (CMP) that can be used to define layouts for viewing and exporting data created from forms.
Currently, this works with form data generated via the Formz extra or FormIt saved forms (using the FormItSaveForm hook).
Other forms or form data saved to custom tables will be added in the future.

The front page lists the forms that exist in the site. By selecting the form you can define a layout to suit your processing requirements.
Once am output definition has been created you can view the existing records and then choose to export the data.
When defining the layout you can select which fields are to be included, change the sequence/order of the fields,
set alternative column titles and if needed set a default value to be used if the value of a field is empty.

The export file will match the defined layout including any required default values irrespective of the form fields saved.

Optionally, you can specify a date range for the export. By default the Start Date will be set to the last exported End Date i.e. just
the latest data.

If you change the structure of a form you will need to remove the existing layout and then define a new form layout.
Note - please ensure you have exported any existing form data that you may need using the existing layout before removing 
and defining a new layout.

This CMP uses the Modx 2.3+ menu system and by default creates a menu entry under the Extras (Components) menu.
