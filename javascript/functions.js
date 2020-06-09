function showLarge(e) {
 open('images/products/'+e.id,'large_image','toolbar=0,status=0,menubar=0,location=0,directories=0')
}

/*
 * Separated by @
 * 1. table name
 * 2. image name
 * 3. price
 * 4. size selected
 * 5. color selected
 */
function sendToCart(data) {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))
    //cookie para poner el producto en el shopping cart
    document.cookie = 'cppepeeshopcart=' + data + '; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

function takeFromCart(data) {
    var d = new Date()
    d.setTime(d.getTime() + (24*60*60*1000))
    //cookie para sacar el producto en el shopping cart
    document.cookie = 'cpsepeeshopcart=' + data + '; expires=' + d.toGMTString() + '; path=/'
    location.reload()
}

function show(id) {
    document.getElementById(id).style.visibility = 'visible';
}

function hide(id) {
    document.getElementById(id).style.visibility = 'hidden';
}