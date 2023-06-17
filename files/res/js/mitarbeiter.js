/* init */
if (document.readyState !== 'loading' ) {
   initMitarbeiter();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initMitarbeiter();
    });
}

var newUser;

function initMitarbeiter() {
    const addNewUserBtn = document.getElementById('addNewUserBtn');

    if (addNewUserBtn === null) {
        return;
    }

    addNewUserBtn.addEventListener('click', function () {
        const addNewUserForm = document.getElementById('addNewUserForm');
        addNewUserForm.classList.toggle('hidden');

        newUser = new NewUser();
    });
}

class NewUser {
    constructor() {
        this.form = document.getElementById('addNewUserForm');
        this.input = this.form.getElementsByTagName('input');
        this.submitBtn = this.form.getElementsByTagName('button')[0];

        this.submitBtn.addEventListener('click', this.submit.bind(this));
    }

    submit(e) {
        e.preventDefault();
        const data = {};
        Array.from(this.input).forEach(i => {
            if (i.value.length === 0) {
                i.classList.add('error');
                return;
            }

            data[i.name] = i.value;
        });

        if (data.newPassword !== data.newPasswordRepeat) {
            return;
        }

        ajax.post({
            r: 'addNewUser',
            username: data.somename,
            password: data.newPassword,
            prename: data.prename,
            lastname: data.lastname,
            email: data.email,
        }).then(() => {
            //window.location.reload();
        });
    }
}
