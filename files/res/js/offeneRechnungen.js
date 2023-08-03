function updateIsDone(id, event) {
    const targetRow = event.currentTarget.parentNode.parentNode;
    const orderId = parseInt(targetRow.children[0].innerHTML);
    const invoiceId = parseInt(targetRow.children[1].innerHTML);

    ajax.post({
        order: orderId,
        invoice: invoiceId,
        r: "setInvoicePaid",
    }).then(res => {
        if (res.status == "success") {
            targetRow.parentNode.removeChild(targetRow);
        }
    });
}
