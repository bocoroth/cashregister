interface Transaction {
  owed: number,
  paid: number,
  order?: number
}

interface ChangeResponse {
  change: string,
  lang: string,
  owed: string,
  paid: string,
  value: string,
  order?: number
}

class Register {
  private outputList: ChangeResponse[]

  constructor() {
    this.outputList = []
    this.initialize()
  }

  public initialize() {
    // add button listeners
    $(`#submitFile`).on('click', () => {
      this.readFile()
    })

    $(`#saveFile`).on('click', () => {
      this.saveFile()
    })
  }

  public readFile(fileInputID = 'amountsFile') {
    let file = $(`#${fileInputID}`).prop('files')[0]
    this.outputList = []

    // read in file as string and display in input box
    file.text().then((fileContents: string) => {
      $(`#submissionSeparator`).show()
      $(`#form-input`).val(fileContents)
      $(`#inputGroup`).show()

      // parse file data
      $.when(this._apiParseFile(fileContents))
      .then((parsedData: Transaction[]) => {
        let deferredList: JQueryPromise<any>[] = []

        // queue up transactions for change processing
        let i = 0
        for (let transaction of parsedData) {
          transaction.order = i
          deferredList.unshift(this._apiGetChange(transaction, i))
          i++
        }

        $.when(...deferredList).then(() => {
          // write response change strings to output

          let out = ''
          $(`#form-output`).val(out)

          this.outputList.sort((a, b) => (a.order > b.order) ? 1 : -1)

          for (let changeResponse of this.outputList) {
            out += changeResponse.change
            if (this.outputList[this.outputList.length - 1] !== changeResponse) {
              out += "\n"
            }
          }

          $(`#form-output`).val(out)
          $(`#outputGroup`).show()
          $(`#saveGroup`).show()
        })
        .fail((jqXHR: JQueryXHR) => {
          this._handleError('An error occurred getting change denominations.', jqXHR)
        })
      })
      .fail((jqXHR: JQueryXHR) => {
        this._handleError('An error occurred parsing file data.', jqXHR)
      })
    })
    .catch((exception: any) => {
      this._handleError('An error occurred reading file data.', exception)
    })
  }

  public saveFile(fileContents: string = null) {
    if (fileContents === null) {
      fileContents = $('#form-output').val().toString()
    }

    let link = document.createElement('a');
    link.download = 'out.txt';
    let blob = new Blob([fileContents], {type: 'text/plain'});
    link.href = window.URL.createObjectURL(blob);
    link.click();
  }

  private _apiParseFile(fileContents: string): JQueryPromise<any> {
    let data = {
      "file_data": fileContents.trim()
    }

    return $.ajax({
      url: `/api/file`,
      type: 'POST',
      data: data
    })
    .fail((jqXHR: JQueryXHR) => {
      this._handleError('An error occurred while submitting your file. Please try again later.', jqXHR)
    })
  }

  private _apiGetChange(transaction: Transaction, order: number): JQueryPromise<any> {
    return $.ajax({
      url: `/api/change/${transaction.owed}/${transaction.paid}`,
      type: 'GET'
    })
    .done((data: ChangeResponse) => {
      data.order = order
      this.outputList.unshift(data)
    })
    .fail((jqXHR: JQueryXHR) => {
      this._handleError('An error occurred getting change.', jqXHR)
    })
  }

  private _handleError(message: string, response: any) {
    console.error(response)  // log response object to console

    // more error handling could go here, such as logging to a file

    this.bootstrapAlert(message, 'danger', true);
  }

  // types: 'success', 'info', 'warning', 'danger'
  public bootstrapAlert (
    message: string,
    type = 'success',
    consoleLog = false,
    delay = 10000,
    fadeIn = 200,
    fadeOut = 500
  ) {
    $(`<div class="alert alert-${type} alert-dismissable" role="alert" style="margin: 20px 20px 0 20px;">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      ${message}
    </div>`)
      .hide()
      .prependTo(`#errorHandler`)
      .fadeIn(fadeIn)
      .delay(delay)
      .fadeOut(fadeOut)

    if (consoleLog) {
      switch (type) {
        case 'info':
          console.info(message)
          break
        case 'warning':
          console.warn(message)
          break
        case 'danger':
          console.error(message)
          break
        default:
          console.log(message)
      }
    }
  }
}
