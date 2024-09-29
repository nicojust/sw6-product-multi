import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class FastOrderPlugin extends Plugin {
    rowContainer;
    rows;
    rawRow;

    static options = {
        separator: ',',
    }

    init() {
        console.debug('Init fast order plugin...');

        const form = this.el;
        this.rowContainer = DomAccess.querySelector(form, '.fast-order-items');
        this.rows = this.queryRows();
        this.rawRow = [...this.rows].reverse()[0];

        const fileUploadButton = DomAccess.querySelector(form, '.fast-order-file-input');
        const newRowButton = DomAccess.querySelector(form, '.fast-order-new-row');

        newRowButton.addEventListener('click', () => {
            this.addNewRow()
        });

        fileUploadButton.addEventListener('change', (event) => {
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = (e) => {
                const csvArray = this.csvToArr(e.target.result, ",");
                this.processCsv(csvArray);
            };

            reader.readAsText(file);
        });
    }

    queryRows() {
        return DomAccess.querySelectorAll(this.rowContainer, '.fast-order-item');
    }

    addNewRow() {
        console.debug('adding new row', this.rowContainer, this.rawRow);
        this.rowContainer.appendChild(this.rawRow.cloneNode(true));
    }

    csvToArr(stringVal, splitter) {
        const [keys, ...rest] = stringVal
            .trim()
            .split("\n")
            .map((item) => item.split(splitter));

        const formedArr = rest.map((item) => {
            const object = {};
            keys.forEach((key, index) => (object[key] = item.at(index)));
            return object;
        });
        return formedArr;
    }

    processCsv(data) {
        if (data.length < 1) {
            alert('Not enough product entries in CSV. There must be at least one product!')
            return
        }

        const numberOfRowsNeeded = data.length - this.queryRows().length;
        if (Math.sign(numberOfRowsNeeded) === 1) {
            for (let i = 0; i < numberOfRowsNeeded; i++) {
                this.addNewRow();
            }
        }

        for (const [index, item] of data.entries()) {
            this.queryRows()[index].querySelectorAll('input')[0].value = item.ProductId
            this.queryRows()[index].querySelectorAll('input')[1].value = item.Qty
        }
    }
}
