<?php

class Icon {

    public static $iconVisible = '<svg style="width: 10px; height: 10px;" viewBox="0 0 24 24"><path fill="currentColor" d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" /></svg>';

    public static $iconInvisible = '<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M11.83,9L15,12.16C15,12.11 15,12.05 15,12A3,3 0 0,0 12,9C11.94,9 11.89,9 11.83,9M7.53,9.8L9.08,11.35C9.03,11.56 9,11.77 9,12A3,3 0 0,0 12,15C12.22,15 12.44,14.97 12.65,14.92L14.2,16.47C13.53,16.8 12.79,17 12,17A5,5 0 0,1 7,12C7,11.21 7.2,10.47 7.53,9.8M2,4.27L4.28,6.55L4.73,7C3.08,8.3 1.78,10 1,12C2.73,16.39 7,19.5 12,19.5C13.55,19.5 15.03,19.2 16.38,18.66L16.81,19.08L19.73,22L21,20.73L3.27,3M12,7A5,5 0 0,1 17,12C17,12.64 16.87,13.26 16.64,13.82L19.57,16.75C21.07,15.5 22.27,13.86 23,12C21.27,7.61 17,4.5 12,4.5C10.6,4.5 9.26,4.75 8,5.2L10.17,7.35C10.74,7.13 11.35,7 12,7Z" /></svg>';

    public static $iconPersonAdd = '<svg style="width:18px;height:18px;vertical-align:bottom;" viewBox="0 0 24 24"><path fill="currentColor" d="M15,14C12.33,14 7,15.33 7,18V20H23V18C23,15.33 17.67,14 15,14M6,10V7H4V10H1V12H4V15H6V12H9V10M15,12A4,4 0 0,0 19,8A4,4 0 0,0 15,4A4,4 0 0,0 11,8A4,4 0 0,0 15,12Z" /></svg>';

    public static $iconOrderAdd = '<svg style="width:18px;height:18px;vertical-align:bottom;" viewBox="0 0 24 24"><path fill="currentColor" d="M17,14H19V17H22V19H19V22H17V19H14V17H17V14M10,2H14A2,2 0 0,1 16,4V6H20A2,2 0 0,1 22,8V13.53C20.94,12.58 19.54,12 18,12A6,6 0 0,0 12,18C12,19.09 12.29,20.12 12.8,21H4C2.89,21 2,20.1 2,19V8C2,6.89 2.89,6 4,6H8V4C8,2.89 8.89,2 10,2M14,6V4H10V6H14Z" /></svg>';

    public static $iconChart = '<svg style="width:18px;height:18px;vertical-align:bottom;" viewBox="0 0 24 24"><path fill="currentColor" d="M16,11.78L20.24,4.45L21.97,5.45L16.74,14.5L10.23,10.75L5.46,19H22V21H2V3H4V17.54L9.5,8L16,11.78Z" /></svg>';

    public static $iconProductAdd = '<svg style="width:18px;height:18px;vertical-align:bottom;" viewBox="0 0 24 24"><path fill="currentColor" d="M5.5,7A1.5,1.5 0 0,1 4,5.5A1.5,1.5 0 0,1 5.5,4A1.5,1.5 0 0,1 7,5.5A1.5,1.5 0 0,1 5.5,7M21.41,11.58L12.41,2.58C12.05,2.22 11.55,2 11,2H4C2.89,2 2,2.89 2,4V11C2,11.55 2.22,12.05 2.59,12.41L11.58,21.41C11.95,21.77 12.45,22 13,22C13.55,22 14.05,21.77 14.41,21.41L21.41,14.41C21.78,14.05 22,13.55 22,13C22,12.44 21.77,11.94 21.41,11.58Z" /></svg>';

    public static $iconNotebook = '<svg style="width:18px;height:18px;vertical-align:bottom;" viewBox="0 0 24 24"><path fill="currentColor" d="M3,7V5H5V4C5,2.89 5.9,2 7,2H13V9L15.5,7.5L18,9V2H19C20.05,2 21,2.95 21,4V20C21,21.05 20.05,22 19,22H7C5.95,22 5,21.05 5,20V19H3V17H5V13H3V11H5V7H3M7,11H5V13H7V11M7,7V5H5V7H7M7,19V17H5V19H7Z" /></svg>';

    public static $iconDelete = '<svg style="width:18px;height:18px" viewBox="0 0 24 24"><path fill="currentColor" d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" /></svg>';

    public static $iconEdit = '<svg style="width:18px;height:18px" viewBox="0 0 24 24"><path fill="currentColor" d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" /></svg>';

    public static $iconAdd = '<svg style="width:18px;height:18px" viewBox="0 0 24 24"><path fill="currentColor" d="M20 14H14V20H10V14H4V10H10V4H14V10H20V14Z" /></svg>';

    public static $iconMove = '<svg style="width:18px;height:18px" viewBox="0 0 24 24"><path fill="currentColor" d="M13,6V11H18V7.75L22.25,12L18,16.25V13H13V18H16.25L12,22.25L7.75,18H11V13H6V16.25L1.75,12L6,7.75V11H11V6H7.75L12,1.75L16.25,6H13Z" /></svg>';

    public static $iconCheck = '<svg style="width:18px;height:18px" viewBox="0 0 24 24">
    <path fill="currentColor" d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z" /></svg>';

    public static $iconInShop = '<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M20 6H4V4H20V6M15.69 14H14V15.69C13.37 16.64 13 17.77 13 19C13 19.34 13.04 19.67 13.09 20H4V14H3V12L4 7H20L21 12V13.35C20.37 13.13 19.7 13 19 13C17.77 13 16.64 13.37 15.69 14M12 14H6V18H12V14M21.34 15.84L17.75 19.43L16.16 17.84L15 19L17.75 22L22.5 17.25L21.34 15.84Z" /></svg>';

    public static $iconNotInShop = '<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M4 4H20V6H4V4M15.46 16.88L16.88 15.46L19 17.59L21.12 15.47L22.54 16.88L20.41 19L22.54 21.12L21.12 22.54L19 20.41L16.88 22.54L15.46 21.12L17.59 19L15.47 16.88M4 7H20L21 12V13.34C20.33 13.09 19.62 12.96 18.91 12.96C17.71 12.96 16.54 13.33 15.54 14H14V15.53C13.3 16.53 12.92 17.73 12.92 18.95L13 20H4V14H3V12L4 7M6 14V18H12V14H6Z" /></svg>';

    public static $iconAddInShop = '<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M4 4V6H20V4M4 7L3 12V14H4V20H13C12.95 19.66 12.92 19.31 12.92 18.95C12.92 17.73 13.3 16.53 14 15.53V14H15.54C16.54 13.33 17.71 12.96 18.91 12.96C19.62 12.96 20.33 13.09 21 13.34V12L20 7M6 14H12V18H6M18 15V18H15V20H18V23H20V20H23V18H20V15" /></svg>';

    public static $iconUpdateInShop = '<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M18 4H2V2H18V4M17.5 13H16V18L19.61 20.16L20.36 18.94L17.5 17.25V13M24 17C24 20.87 20.87 24 17 24C13.47 24 10.57 21.39 10.08 18H2V12H1V10L2 5H18L19 10V10.29C21.89 11.16 24 13.83 24 17M4 16H10V12H4V16M22 17C22 14.24 19.76 12 17 12S12 14.24 12 17 14.24 22 17 22 22 19.76 22 17Z" /></svg>';

    public static $iconEditText = '<svg style="width:10px;height:10px" viewBox="0 0 24 24"><path fill="currentColor" d="M15 16L11 20H21V16H15M12.06 7.19L3 16.25V20H6.75L15.81 10.94L12.06 7.19M18.71 8.04C19.1 7.65 19.1 7 18.71 6.63L16.37 4.29C16.17 4.09 15.92 4 15.66 4C15.41 4 15.15 4.1 14.96 4.29L13.13 6.12L16.88 9.87L18.71 8.04Z" /></svg>';

    public static $iconConnectTo = '<svg xmlns="http://www.w3.org/2000/svg" style="width: 10px; height 10px" viewBox="0 0 24 24"><path d="M10.6 13.4A1 1 0 0 1 9.2 14.8A4.8 4.8 0 0 1 9.2 7.8L12.7 4.2A5.1 5.1 0 0 1 19.8 4.2A5.1 5.1 0 0 1 19.8 11.3L18.3 12.8A6.4 6.4 0 0 0 17.9 10.4L18.4 9.9A3.2 3.2 0 0 0 18.4 5.6A3.2 3.2 0 0 0 14.1 5.6L10.6 9.2A2.9 2.9 0 0 0 10.6 13.4M23 18V20H20V23H18V20H15V18H18V15H20V18M16.2 13.7A4.8 4.8 0 0 0 14.8 9.2A1 1 0 0 0 13.4 10.6A2.9 2.9 0 0 1 13.4 14.8L9.9 18.4A3.2 3.2 0 0 1 5.6 18.4A3.2 3.2 0 0 1 5.6 14.1L6.1 13.7A7.3 7.3 0 0 1 5.7 11.2L4.2 12.7A5.1 5.1 0 0 0 4.2 19.8A5.1 5.1 0 0 0 11.3 19.8L13.1 18A6 6 0 0 1 16.2 13.7Z" /></svg>';

    public static $iconCategory = '<svg xmlns="http://www.w3.org/2000/svg" style="width: 10px; height 10px" viewBox="0 0 24 24"><path d="M3,3H9V7H3V3M15,10H21V14H15V10M15,17H21V21H15V17M13,13H7V18H13V20H7L5,20V9H7V11H13V13Z" /></svg>';

}

?>