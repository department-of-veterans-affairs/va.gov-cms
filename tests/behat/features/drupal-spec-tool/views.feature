@api
Feature: Views
  In order to present and expose content and configuration
  As a site owner
  I want to have views for various contexts and applications.

  @spec @views
  Scenario: Views
    Then exactly the following views should exist
      | Name                                  | Machine name                           | Base table        | Status   | Description                                                                                   |
      | Archive                               | archive                                | Content           | Disabled | All content, by month.                                                                        |
      | Content                               | content                                | Content           | Enabled  | Find and manage content.                                                                      |
      | Custom block library                  | block_content                          | Custom Block      | Enabled  | Find and manage custom blocks.                                                                |
      | Files                                 | files                                  | Files             | Enabled  | Find and manage files.                                                                        |
      | Frontpage                             | frontpage                              | Content           | Enabled  | All content promoted to frontpage                                                             |
      | Glossary                              | glossary                               | Content           | Disabled | All content, by letter.                                                                       |
      | Media                                 | media                                  | Media             | Enabled  |                                                                                               |
      | Moderated content                     | moderated_content                      | Content revisions | Enabled  | Find and moderate content.                                                                    |
      | Moderation history                    | moderation_history                     | Content revisions | Enabled  |                                                                                               |
      | People                                | user_admin_people                      | Users             | Enabled  | Find and manage people interacting with your site.                                            |
      | Recent content                        | content_recent                         | Content           | Enabled  | Recent content.                                                                               |
      | Search                                | search                                 | Index Content     | Enabled  |                                                                                               |
      | Taxonomy term                         | taxonomy_term                          | Content           | Enabled  | Content belonging to a certain taxonomy term.                                                 |
      | Watchdog                              | watchdog                               | Log entries       | Enabled  | Recent log messages                                                                           |
      | Who's new                             | who_s_new                              | Users             | Enabled  | Shows a list of the newest user accounts on the site.                                         |
      | Who's online block                    | who_s_online                           | Users             | Enabled  | Shows the user names of the most recently active users, and the total number of active users. |
      | Media library                         | media_library                          | Media             | Enabled  |                                                                                               |
      | Redirect                              | redirect                               | Redirect          | Enabled  | List of redirects                                                                             |
      | Blocks listing                        | va_blocks_admin                        | Custom Block      | Enabled  | Shows existing blocks on the site.                                                            |
      | Build info                            | build_info                             | Content           | Enabled  |                                                                                               |
      | Moderation Dashboard In Review        | content_moderation_dashboard_in_review | Content revisions | Enabled  |                                                                                               |
      | Moderation Dashboard Recent Changes   | moderation_dashboard_recent_changes    | Content revisions | Enabled  |                                                                                               |
      | Moderation Dashboard Recently Created | moderation_dashboard_recently_created  | Content           | Enabled  |                                                                                               |

  @spec @views
  Scenario: Views displays
    Then exactly the following views displays should exist
      | View                                  | Title               | Machine name       | Display plugin |
      | Archive                               | Master              | default            | Master         |
      | Archive                               | Block               | block_1            | Block          |
      | Archive                               | Page                | page_1             | Page           |
      | Custom block library                  | Master              | default            | Master         |
      | Custom block library                  | Page                | page_1             | Page           |
      | Content                               | Master              | default            | Master         |
      | Content                               | Page                | page_1             | Page           |
      | Recent content                        | Master              | default            | Master         |
      | Recent content                        | Block               | block_1            | Block          |
      | Files                                 | Master              | default            | Master         |
      | Files                                 | Files overview      | page_1             | Page           |
      | Files                                 | File usage          | page_2             | Page           |
      | Frontpage                             | Master              | default            | Master         |
      | Frontpage                             | Feed                | feed_1             | Feed           |
      | Frontpage                             | Page                | page_1             | Page           |
      | Glossary                              | Master              | default            | Master         |
      | Glossary                              | Attachment          | attachment_1       | Attachment     |
      | Glossary                              | Page                | page_1             | Page           |
      | Media                                 | Master              | default            | Master         |
      | Media                                 | Browser             | entity_browser_1   | Entity browser |
      | Media                                 | Image Browser       | entity_browser_2   | Entity browser |
      | Media                                 | Media               | media_page_list    | Page           |
      | Media library                         | Master              | default            | Master         |
      | Media library                         | Page                | page               | Page           |
      | Media library                         | Widget              | widget             | Page           |
      | Moderated content                     | Master              | default            | Master         |
      | Moderated content                     | Moderated content   | moderated_content  | Page           |
      | Moderation history                    | Master              | default            | Master         |
      | Moderation history                    | Page                | page               | Page           |
      | Search                                | Master              | default            | Master         |
      | Search                                | Page                | page               | Page           |
      | Taxonomy term                         | Master              | default            | Master         |
      | Taxonomy term                         | Feed                | feed_1             | Feed           |
      | Taxonomy term                         | Page                | page_1             | Page           |
      | People                                | Master              | default            | Master         |
      | People                                | Page                | page_1             | Page           |
      | Watchdog                              | Master              | default            | Master         |
      | Watchdog                              | Page                | page               | Page           |
      | Who's new                             | Master              | default            | Master         |
      | Who's new                             | Who's new           | block_1            | Block          |
      | Who's online block                    | Master              | default            | Master         |
      | Who's online block                    | Who's online        | who_s_online_block | Block          |
      | Blocks listing                        | Promo blocks        | page_1             | Page           |
      | Blocks listing                        | Alert Blocks        | page_2             | Page           |
      | Blocks listing                        | Master              | default            | Master         |
      | Redirect                              | Master              | default            | Master         |
      | Redirect                              | Page                | page_1             | Page           |
      | Build info                            | Master              | default            | Master         |
      | Build info                            | REST export         | rest_export_1      | REST export    |
      | Moderation Dashboard In Review        | In draft            | block_2            | Block          |
      | Moderation Dashboard In Review        | In review           | block_1            | Block          |
      | Moderation Dashboard In Review        | Master              | default            | Master         |
      | Moderation Dashboard In Review        | Your drafts         | block_3            | Block          |
      | Moderation Dashboard Recent Changes   | Master              | default            | Master         |
      | Moderation Dashboard Recent Changes   | Recent Changes      | block_1            | Block          |
      | Moderation Dashboard Recent Changes   | Your activity       | block_2            | Block          |
      | Moderation Dashboard Recently Created | Content you created | block_2            | Block          |
      | Moderation Dashboard Recently Created | Master              | default            | Master         |
      | Moderation Dashboard Recently Created | Recently created    | block_1            | Block          |
