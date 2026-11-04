//
document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-cart-quantity]');

    if (!input) {
        return;
    }

    const row = input.closest('[data-cart-row]');
    const subtotal = row.querySelector('[data-cart-subtotal]');
    const total = document.querySelector('[data-cart-total]');
    const price = Number(row.dataset.cartPrice);
    const quantity = Math.min(
        Math.max(Number(input.value) || Number(input.min) || 1, Number(input.min) || 1),
        Number(input.max),
    );

    input.value = quantity;
    subtotal.textContent = formatCurrency(price * quantity);

    const totalValue = [...document.querySelectorAll('[data-cart-row]')]
        .reduce((sum, cartRow) => sum + Number(cartRow.dataset.cartPrice)
            * Number(cartRow.querySelector('[data-cart-quantity]').value || 0), 0);

    total.textContent = formatCurrency(totalValue);
});

document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-cart-quantity]');

    if (input) {
        input.form.requestSubmit();
    }
});

const catalogSearchInput = document.querySelector('#catalog-search-input');

if (catalogSearchInput) {
    const catalogProducts = [...document.querySelectorAll('.catalog-product-card')];
    const noResults = document.querySelector('#catalog-no-results');

    catalogSearchInput.addEventListener('input', () => {
        const search = catalogSearchInput.value.trim().toLocaleLowerCase();
        let visibleProducts = 0;

        catalogProducts.forEach((product) => {
            const name = product.dataset.productName.toLocaleLowerCase();
            const matches = search === '' || name.includes(search);

            product.hidden = !matches;
            visibleProducts += matches ? 1 : 0;
        });

        if (noResults) {
            noResults.textContent = noResults.dataset.noResultsTemplate.replace(
                ':search',
                catalogSearchInput.value.trim(),
            );
            noResults.hidden = visibleProducts > 0;
        }
    });

    catalogSearchInput.dispatchEvent(new Event('input'));
}

function formatCurrency(value) {
    return `$${value.toLocaleString('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}