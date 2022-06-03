mod admin;
mod nodes;

use goose::prelude::*;

use crate::admin::*;
use crate::nodes::*;

/// Defines the actual load test. Each task set simulates a type of user.
///  - Anonymous English user: loads the English version of all pages
///  - Anonymous Spanish user: loads the Spanish version of all pages
///  - Admin user: load pages as logged-in user, including editing a node
#[tokio::main]
async fn main() -> Result<(), GooseError> {
    let _goose_metrics = GooseAttack::initialize()?
        // .register_scenario(
        //     scenario!("Anonymous English user")
        //         .set_weight(40)?
        //         // .set_wait_time(Duration::from_secs(0), Duration::from_secs(1))?
        //         .register_transaction(
        //             transaction!(benefits_detail_page)
        //                 .set_name("anon /")
        //                 .set_weight(2)?,
        //         ),
        // )
        .register_scenario(
            scenario!("Admin user")
                .set_weight(1)?
                // .set_wait_time(Duration::from_secs(0), Duration::from_secs(1))?
                .register_transaction(
                    transaction!(log_in)
                        .set_on_start()
                        .set_name("auth login"),
                )
                .register_transaction(
                    transaction!(benefits_detail_page)
                      .set_name("auth /disability/how-to-file-claim"),
                ),
        )
        .set_default(GooseDefault::Host, "https://drupal-9.ddev.site/")?
        .execute()
        .await?;

    Ok(())
}
