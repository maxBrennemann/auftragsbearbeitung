function print() {
    var win = window.open("", "", "width=900,height=700");
    win.document.write("<h1>Welcome to Print Page</h1>");
    win.document.close();
    win.focus();
    win.print();
    win.close();
}