<div class='blackout' style='display:none'></div>
<div class='asset-dialog-wrapper' style='display:none'>
  <div class='asset-dialog'>
    <div class='asset-dialog-title-bar'>
      
      <ul class='asset-dialog-tabs'>
        <li><a href='#'>Edit 'Some File'</a></li>
        <li><a href='#'>Browse</a></li>
        <li><a href='#'>Upload Queue (5)</a></li>
      </ul>
    </div>
    <div class='asset-dialog-browser asset-dialog-main'>
      <div class='asset-dialog-places'>
        <dl>
          <dt>Local Assets</dt>
          <dd><a href='#'><%= icon(:star) %> Starred</a></dd>
          <dd><a href='#'><%= icon(:clock) %> Recent</a></dd>
          <dd>
            <ul>
              <li class='active'>
                <a href='#'><%= icon(:folder) %> Folders</a>
                <ul>
                  <li><a href='#'><%= icon(:folder) %> Folder 1</a></li>
                  <li>
                    <a href='#'><%= icon(:folder) %> Folder 2</a>
                    <ul>
                      <li><a href='#'><%= icon(:folder) %> Folder 4</a></li>
                      <li><a href='#'><%= icon(:folder) %> Folder 5</a></li>
                      <li><a href='#'><%= icon(:folder) %> Folder 6</a></li>
                    </ul>
                  </li>
                  <li><a href='#'><%= icon(:folder) %> Folder 3</a></li>
                </ul>
              </li>
            </ul>
          </dd>
          <dt>S3 Assets</dt>
          <dd><a href='#'><%= icon(:network) %> Assets</a></dd>
        </dl>
      </div>
      <div class='asset-dialog-path'>
        Asset Folder <span class='separator'>&#x27a4;</span>
        Folder 1 <span class='separator'>&#x27a4;</span>
        Folder 2
      </div>
    </div>
    <div class='asset-dialog-upload-queue asset-dialog-main' style='display:none'>
      
    </div>
    <div class='asset-dialog-sidebar'>
      <ul class='asset-dialog-tabs'>
        <li class='asset-dialog-tab-preview'><a href='#'>Preview</a></li>
        <li class='asset-dialog-tab-selection'><a href='#'>Selection</a></li>
      </ul>
      <div class='asset-dialog-sidebar-inner'>
        <div class='asset-dialog-preview-pane'>
        </div>
      
        <table class='asset-dialog-preview-meta'>
          <tr>
            <th>Filename:</th>
            <td>foobar.gif</td>
          </tr>
          <tr>
            <th>Title:</th>
            <td>A picture of a Foobar</td>
          </tr>
          <tr>
            <th>Size:</th>
            <td>112 KB</td>
          </tr>
          <tr>
            <th>Dimensions:</th>
            <td>500 &times; 500</td>
          </tr>
        </table>
      
        <button type='button'><%= icon(:document_zipper) %> Expand Archive</button>
        <button type='button'><%= icon(:pencil) %> Edit File</button><br />
      </div>
      
    </div>
    <div class='asset-dialog-footer'>
      <table>
        <tr>
          <td class='asset-dialog-buttons'>
            <button type='button' disabled='true'><%= icon(:tick_circle) %> Select Files</button>
            <button type='button'><%= icon(:plus_circle) %> Upload New File</button>
            <button type='button'><%= icon(:cross) %> Cancel</button>
            <button type='button' class='red'><%= icon(:minus_circle) %> Delete Page</button>
            <button type='button' class='blue'>Close</button>
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>