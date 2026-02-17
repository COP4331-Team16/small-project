const urlBase = 'http://portcall.cloud/LAMPAPI';
const extension = 'php';

let userId = 0;
let firstName = "";
let lastName = "";

function doLogin() {
	userId = 0;
	firstName = "";
	lastName = "";

	let login = document.getElementById("loginName").value;
	let password = document.getElementById("loginPassword").value;
	//	var hash = md5( password );

	document.getElementById("loginResult").innerHTML = "";

	let tmp = { login: login, password: password };
	//	var tmp = {login:login,password:hash};
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/Login.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);
				userId = jsonObject.id;

				if (userId < 1) {
					document.getElementById("loginResult").innerHTML = "User/Password combination incorrect";
					return;
				}

				firstName = jsonObject.firstName;
				lastName = jsonObject.lastName;

				saveCookie();

				window.location.href = "contacts.html";
			}
		};
		xhr.send(jsonPayload);
	}
	catch (err) {
		document.getElementById("loginResult").innerHTML = err.message;
	}

}

function saveCookie() {
	let minutes = 20;
	let date = new Date();
	date.setTime(date.getTime() + (minutes * 60 * 1000));
	document.cookie = "firstName=" + firstName + ",lastName=" + lastName + ",userId=" + userId + ";expires=" + date.toGMTString();
}

function readCookie() {
	userId = -1;
	let data = document.cookie;
	let splits = data.split(",");
	for (var i = 0; i < splits.length; i++) {
		let thisOne = splits[i].trim();
		let tokens = thisOne.split("=");
		if (tokens[0] == "firstName") {
			firstName = tokens[1];
		}
		else if (tokens[0] == "lastName") {
			lastName = tokens[1];
		}
		else if (tokens[0] == "userId") {
			userId = parseInt(tokens[1].trim());
		}
	}

	if (userId < 0) {
		window.location.href = "index.html";
	}
	else {
		document.getElementById("userName").innerHTML = "Logged in as " + firstName + " " + lastName;
		loadContacts();
	}
}

function doLogout() {
	userId = 0;
	firstName = "";
	lastName = "";
	document.cookie = "firstName= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
	window.location.href = "index.html";
}

function addContact() {
	readCookie();

	let firstName = document.getElementById("firstName").value;
	let lastName = document.getElementById("lastName").value;
	let email = document.getElementById("email").value;
	let phone = document.getElementById("phone").value;
	document.getElementById("contactAddResult").innerHTML = "";

	let tmp = { firstName: firstName, lastName: lastName, email: email, phone: phone, userId: userId };
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/AddContact.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);
				if (jsonObject.error && jsonObject.error !== "") {
					document.getElementById("contactAddResult").innerHTML = jsonObject.error;
				}
				else {
					document.getElementById("contactAddResult").innerHTML = "Contact has been added";
					// Clear the form fields
					document.getElementById("firstName").value = "";
					document.getElementById("lastName").value = "";
					document.getElementById("email").value = "";
					document.getElementById("phone").value = "";
					// Refresh the contacts table
					loadContacts();
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch (err) {
		document.getElementById("contactAddResult").innerHTML = err.message;
	}

}

function searchContacts() {
	let srch = document.getElementById("searchText").value;
	document.getElementById("contactSearchResult").innerHTML = "";

	let contactList = "";

	let tmp = { search: srch, userId: userId };
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/SearchContacts.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				document.getElementById("contactSearchResult").innerHTML = "Contact(s) has been retrieved";
				let jsonObject = JSON.parse(xhr.responseText);

				var tbody = document.getElementById("tbody");
				tbody.innerHTML = "";
				for (let i = 0; i < jsonObject.results.length; i++) {
					var row = tbody.insertRow(i);
					var firstName = row.insertCell(0);
					var lastName = row.insertCell(1);
					var email = row.insertCell(2);
					var phone = row.insertCell(3);

					firstName.innerHTML = jsonObject.results[i].firstName;
					lastName.innerHTML = jsonObject.results[i].lastName;
					email.innerHTML = jsonObject.results[i].email;
					phone.innerHTML = jsonObject.results[i].phone;
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch (err) {
		document.getElementById("contactSearchResult").innerHTML = err.message;
	}

}

function loadContacts() {
	let tmp = { search: "", userId: userId };
	let jsonPayload = JSON.stringify(tmp);

	let url = urlBase + '/SearchContacts.' + extension;

	let xhr = new XMLHttpRequest();
	xhr.open("POST", url, true);
	xhr.setRequestHeader("Content-type", "application/json; charset=UTF-8");
	try {
		xhr.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				let jsonObject = JSON.parse(xhr.responseText);

				let tbody = document.getElementById("tbody");
				tbody.innerHTML = "";

				if (jsonObject.results && jsonObject.results.length > 0) {
					for (let i = 0; i < jsonObject.results.length; i++) {
						let row = tbody.insertRow(i);
						let firstName = row.insertCell(0);
						let lastName = row.insertCell(1);
						let email = row.insertCell(2);
						let phone = row.insertCell(3);

						firstName.innerHTML = jsonObject.results[i].firstName;
						lastName.innerHTML = jsonObject.results[i].lastName;
						email.innerHTML = jsonObject.results[i].email;
						phone.innerHTML = jsonObject.results[i].phone;
					}
				}
			}
		};
		xhr.send(jsonPayload);
	}
	catch (err) {
		console.error("Error loading contacts: " + err.message);
	}
}

function showTable() {
	loadContacts();
}
