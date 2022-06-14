use goose::prelude::*;

/// Load a Benefits Detail Page and all static assets found on the page.
pub async fn benefits_detail_page(user: &mut GooseUser) -> TransactionResult {
    let goose = user.get("/disability/how-to-file-claim").await?;
    goose_eggs::validate_and_load_static_assets(
        user,
        goose,
        &goose_eggs::Validate::builder().title("How to file").build(),
    )
    .await?;

    Ok(())
}
