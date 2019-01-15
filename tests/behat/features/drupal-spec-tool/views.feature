@api
Feature: Views
  In order to present and expose content and configuration
  As a site owner
  I want to have views for various contexts and applications.

  @spec @views
  Scenario: Views
    Then exactly the following views should exist
      | Name                 | Machine name       | Base table        | Status   | Description                                                                                   |
      | Archive              | archive            | Content           | Disabled | All content, by month.                                                                        |
      | Contact messages     | contact_messages   | Contact message   | Enabled  | View and manage messages sent through contact forms.                                          |
      | Content              | content            | Content           | Enabled  | Find and manage content.                                                                      |
      | Custom block library | block_content      | Custom Block      | Enabled  | Find and manage custom blocks.                                                                |
      | Files                | files              | Files             | Enabled  | Find and manage files.                                                                        |
      | Frontpage            | frontpage          | Content           | Enabled  | All content promoted to frontpage                                                             |
      | Glossary             | glossary           | Content           | Disabled | All content, by letter.                                                                       |
      | Media                | media              | Media             | Enabled  |                                                                                               |
      | Moderated content    | moderated_content  | Content revisions | Enabled  | Find and moderate content.                                                                    |
      | Moderation history   | moderation_history | Content revisions | Enabled  |                                                                                               |
      | People               | user_admin_people  | Users             | Enabled  | Find and manage people interacting with your site.                                            |
      | Recent content       | content_recent     | Content           | Enabled  | Recent content.                                                                               |
      | Search               | search             | Index Content     | Enabled  |                                                                                               |
      | Taxonomy term        | taxonomy_term      | Content           | Enabled  | Content belonging to a certain taxonomy term.                                                 |
      | Watchdog             | watchdog           | Log entries       | Enabled  | Recent log messages                                                                           |
      | Who's new            | who_s_new          | Users             | Enabled  | Shows a list of the newest user accounts on the site.                                         |
      | Who's online block   | who_s_online       | Users             | Enabled  | Shows the user names of the most recently active users, and the total number of active users. |
      | Media library        | media_library      | Media             | Enabled  |                                                                                               |

  @spec @views
  Scenario: Views displays
    Then exactly the following views displays should exist
      | View                 | Title             | Machine name       | Display plugin |
      | Archive              | Master            | default            | Master         |
      | Archive              | Block             | block_1            | Block          |
      | Archive              | Page              | page_1             | Page           |
      | Custom block library | Master            | default            | Master         |
      | Custom block library | Page              | page_1             | Page           |
      | Contact messages     | Master            | default            | Master         |
      | Contact messages     | Page              | page_1             | Page           |
      | Content              | Master            | default            | Master         |
      | Content              | Page              | page_1             | Page           |
      | Recent content       | Master            | default            | Master         |
      | Recent content       | Block             | block_1            | Block          |
      | Files                | Master            | default            | Master         |
      | Files                | Files overview    | page_1             | Page           |
      | Files                | File usage        | page_2             | Page           |
      | Frontpage            | Master            | default            | Master         |
      | Frontpage            | Feed              | feed_1             | Feed           |
      | Frontpage            | Page              | page_1             | Page           |
      | Glossary             | Master            | default            | Master         |
      | Glossary             | Attachment        | attachment_1       | Attachment     |
      | Glossary             | Page              | page_1             | Page           |
      | Media                | Master            | default            | Master         |
      | Media                | Browser           | entity_browser_1   | Entity browser |
      | Media                | Image Browser     | entity_browser_2   | Entity browser |
      | Media                | Media             | media_page_list    | Page           |
      | Media library        | Master            | default            | Master         |
      | Media library        | Page              | page               | Page           |
      | Media library        | Widget            | widget             | Page           |
      | Moderated content    | Master            | default            | Master         |
      | Moderated content    | Moderated content | moderated_content  | Page           |
      | Moderation history   | Master            | default            | Master         |
      | Moderation history   | Page              | page               | Page           |
      | Search               | Master            | default            | Master         |
      | Search               | Page              | page               | Page           |
      | Taxonomy term        | Master            | default            | Master         |
      | Taxonomy term        | Feed              | feed_1             | Feed           |
      | Taxonomy term        | Page              | page_1             | Page           |
      | People               | Master            | default            | Master         |
      | People               | Page              | page_1             | Page           |
      | Watchdog             | Master            | default            | Master         |
      | Watchdog             | Page              | page               | Page           |
      | Who's new            | Master            | default            | Master         |
      | Who's new            | Who's new         | block_1            | Block          |
      | Who's online block   | Master            | default            | Master         |
      | Who's online block   | Who's online      | who_s_online_block | Block          |
