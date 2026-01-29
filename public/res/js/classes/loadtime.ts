const timeElement = document.querySelector("data#loadtime") as HTMLDataElement;

if (timeElement) {
    const time = timeElement.value;
    console.log(`[INFO] Page loaded in ${time} seconds`);
}
