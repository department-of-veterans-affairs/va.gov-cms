use goose::prelude::*;

use std::env;

/// Log into the website.
pub async fn log_in(user: &mut GooseUser) -> TransactionResult {
    // Use ADMIN_USERNAME= to set custom admin username.
    let admin_username = match env::var("ADMIN_USERNAME") {
        Ok(username) => username,
        Err(_) => "CMS Migrator".to_string(),
    };
    // Use ADMIN_PASSWORD= to set custom admin username.
    let admin_password = match env::var("ADMIN_PASSWORD") {
        Ok(password) => password,
        Err(_) => "drupal8".to_string(),
    };

    let login = goose_eggs::drupal::Login::builder()
        .username(&*admin_username)
        .password(&*admin_password)
        .url("/")
        .build();
    goose_eggs::drupal::log_in(user, &login).await?;

    Ok(())
}
