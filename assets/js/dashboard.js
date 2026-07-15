document.addEventListener("DOMContentLoaded", () => {
    /*
    ============================
    PROJECT CHART
    ============================
    */
    const projectCanvas =
        document.getElementById("projectChart");
    if (
        projectCanvas &&
        window.projectChartData
    ) {
        const projectLabels =
            window.projectChartData.map(
                item => item.status
            );
        const projectValues =
            window.projectChartData.map(
                item => parseInt(item.total)
            );
        new Chart(projectCanvas, {
            type: "doughnut",
            data: {
                labels: projectLabels,
                datasets: [
                    {
                        data: projectValues,
                        backgroundColor: [
                            "#f97316",
                            "#2563eb",
                            "#8b5cf6",
                            "#16a34a",
                            "#ef4444",
                            "#06b6d4"
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom"
                    }
                }
            }
        });
    }
    /*
    ============================
    TASK CHART
    ============================
    */
    const taskCanvas =
        document.getElementById("taskChart");
    if (
        taskCanvas &&
        window.taskChartData
    ) {
        const taskLabels =
            window.taskChartData.map(
                item => item.status
            );
        const taskValues =
            window.taskChartData.map(
                item => parseInt(item.total)
            );
        new Chart(taskCanvas, {
            type: "bar",
            data: {
                labels: taskLabels,
                datasets: [
                    {
                        label: "Tasks",
                        data: taskValues,
                        backgroundColor: [
                            "#ef4444",
                            "#2563eb",
                            "#f97316",
                            "#16a34a",
                            "#8b5cf6",
                            "#06b6d4"
                        ]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
});