# Translation overview
This document outlines how to select content for translation, send it for translation, import the results, and review and manage the imported translations.

## Sources

The [Sources](https://prod.cms.va.gov/admin/tmgmt/sources) page provides an overview of all content in the CMS that is translatable and its translation state. The page defaults to listing nodes, but anything that can be translated can be listed here. 

![image](https://user-images.githubusercontent.com/203623/231054441-f8fd1bf4-bd96-40a6-bc0e-e3f12c4f9dab.png)


In this overview, we can see:

* At top, a number of filters and search fields for limiting what content is displayed in the Sources list
* Tools for Checkout and ‘Add to cart’ functionality, to start the translation process for selected pieces of content.
* The listing of content itself.

There are also a number of icons indicating the translation state of each piece of content for each target language. We might see:

* Gray X for not translated, green √ for translated
* A blue hourglass, indicating that the content is part of an active translation job
* A yellow caution sign, indicating that the translation has been received and is in review. 

Multiple icons can exist for a single piece of content; for example, a translation may exist for a piece of content, but also that content can contain updates and be mid-translation.

In order to translate content, select its checkbox, and then use either the Checkout or Add to Cart functionality. 

![image](https://user-images.githubusercontent.com/203623/231054552-ae4b8965-523e-4be2-8cd7-b4c568db3118.png)


Adding a label is optional but recommended. It will help you identify the content later.

Make sure that you have selected a target language. 

Your job may add items to ‘Suggestions’. It is not recommended to add Suggestions in with a given job, but it is useful for identifying other content that you may want to add to additional jobs.

Once you’ve created your job, there will be message that you can download the file with your translation data: 
![image](https://user-images.githubusercontent.com/203623/231054596-43e9e24d-b25b-4c35-b104-69edf64295b7.png)

Right-click on the link and select ‘Save as…’. This file will be what gets sent to translation.

## Translation via file

You can rename the files if it helps you keep organized. We do recommend keeping “JobID24” for example, to help you associate the file with its job. 

When the files have been processed by the translators and returned, they are ready to be imported. 


## Jobs and file import

Each collection of content that resulted in a file to download is a ‘job’. You can see jobs at the [job overview page](https://prod.cms.va.gov/admin/tmgmt/jobs).
![image](https://user-images.githubusercontent.com/203623/231054663-312a621c-75f7-4c48-b78d-18848bb59ab9.png)


You can see each job with its label, the source and target languages, the job progress, and numbers of words and HTML tags. The progress numbers are by segments, rather than words. 

A job that has not imported a file will show blue. A job that has had its file imported will show some combination of yellow and green, depending on how much of the content has been reviewed.

To import the file for a given job, click on ‘Manage’ for that job. You will see the job overview:
![image](https://user-images.githubusercontent.com/203623/231054700-702e52d0-a79e-4284-8059-06cac69fadc4.png)



This shows each job item and its status. To import a file, scroll further down:

![image](https://user-images.githubusercontent.com/203623/231054740-f637d56a-e985-4a48-8d9c-e7c58ca044af.png)


To import the file, simply select it via the file upload widget, and click import. Once you do this, each job item can be reviewed. 


## Review
![image](https://user-images.githubusercontent.com/203623/231054805-4ec594b9-910b-4ae0-9dca-ef851bb08002.png)



This is an opportunity for a language expert to review the translation to ensure it is correct. It is possible to make changes here, if desired. Click the checkmark on the right if the translation is satisfactory.

Once all translations have been reviewed, you can accept them, at the bottom of the form:

![image](https://user-images.githubusercontent.com/203623/231054823-cb759482-2dbc-4c4b-84ff-b98d9e11b6d1.png)



Note that the interface will show you the current moderation state of the source content - draft, published, etc - and allow you to decide what moderation state the translation should be in. You also have the option to validate the translation (this will mainly check for empty fields) and to specifically validate the HTML tags in the translation.

When you are satisfied with the translation, save it as completed.


## Editing translations after import

You will sometimes need to edit translations after they have been imported. This can be done from the main content page.

Take this example page:

![image](https://user-images.githubusercontent.com/203623/231054844-0ae91795-5bc8-4ce7-8c6a-91ca4d6fed25.png)


In addition to options for View, Edit, etc, there is a tab ‘Translate’. Click this to get an overview of translations for this content.


![image](https://user-images.githubusercontent.com/203623/231054869-3c3ae9f8-b30b-426d-846a-45374588bb1a.png)


Here you can see each translation, and edit it if you choose. Note that only translatable fields will be editable on the translation edit form. This is where you would publish the translation if it is not published already.
